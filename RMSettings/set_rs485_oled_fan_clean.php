<?php
error_reporting(E_ALL);

function checksum16($data) {
    $checksum = 0;
    for ($i = 0; $i < 24; $i++) {
        $hex = implode(unpack("H*", $data[$i]));
        $c = $checksum;
        $checksum ^= hexdec($hex);
        // echo "c: " . $c . ", hex: " . $hex . ", checksum: " . $checksum . "\n";
    }
    echo "checksum = " . dechex($checksum) . "\n";
    if($checksum>15)
    return pack("H*", dechex($checksum));
    else
	return pack("h*", dechex($checksum));
}



function sendCommand($socket, $padding_char, $cmd , $ch1 ,$rs485_id) {
    $data = array_fill(0, 25, $padding_char);
    $data[0] = 'F';
    $data[1] = 'A';
    $data[2] = $cmd;
	$data[3] = $ch1;
	$data[4] =  pack("h*", dechex($rs485_id)); //CHx RS485 ID1 如果ID >0X10 要改成=>  pack("H*", 大寫H            
	$data[5] =  pack("h*", dechex(0x03)); //OLED 風扇控制 :0x00=POWER_OFF ,0x01=POWER_ON, 0x02=KEEP,0x03=CLEAN 0x06=SET TIME&FAN
	$data[6] =  pack("h*", dechex(0x00)); //BALANCE_VALUE 平衡值 (READ_ONLY)
	$data[7] =  pack("H*", dechex(0x1E)); //FAN_SPEED 設定風扇速度  EX:30% =30=0x1E
	$data[8] =  pack("H*", dechex(0x20)); //CLEAN_TIMMER 設定清潔時間 EX:32小時清潔一次=32=0x20
	$data[9] =  pack("h*", dechex(0x00)); //ALARM_LEFT (READ_ONLY)
	$data[10] = pack("h*", dechex(0x00)); //ALARM RIGHT (READ_ONLY)
	$data[11] = pack("h*", dechex(0x00)); //KEEP
	$data[12] = pack("h*", dechex(0x00)); //KEEP
	
    $data[24] =checksum16(implode('', $data));
    $message = implode('', $data);

    echo "Send HEX CMD: " . bin2hex($message) . "\n";

    $result = socket_send($socket, $message, strlen($message), 0);
    if ($result === false) {
        echo "socket_send() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
        socket_close($socket);
        echo "Socket Closed\n";
        exit();
    } else {
        echo "Sent..." . $result . "\n";
    }
}


echo "SCAN ALL CH RS485 ID...\n";

$address = isset($argv[1])? $argv[1] : '192.168.1.123';

$service_port = isset($argv[2])? (int)$argv[2] : 4001;




$cmd = '1';
if (isset($argv[3])) {
    $cmd = $argv[3];
}

$ch1 = '1';
if (isset($argv[4])) {
    $ch1 = $argv[4];
}

$rs485_id=1;
if (isset($argv[5])) {
    $rs485_id = $argv[5];
}



$read = false;
if (isset($argv[6])) {
    $read = true;
}

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) {
    echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
    exit();
} 

echo "Attempting to connect to '$address' on port '$service_port'...\n";
$result = socket_connect($socket, $address, $service_port);
if ($result === false) {
    echo "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
    socket_close($socket);
    echo "Socket Closed\n";
    exit();
} else {
    echo "Connected.\n";
}

sendCommand($socket, 'U', $cmd ,$ch1,$rs485_id);

//sleep(1);

$ADDR = '2';

if ($read) {
	
    while(true) {
        $result = socket_read($socket, 24);
        if ($result === false) {
            echo "socket_read() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
        } else {
            echo "Get SCAN ALL CH RS485 ID ANS  hex data: " . bin2hex($result) . "\n";
			echo "\n";
			if ($result[0] == 'Z' && $result[1] == 'X' ) {
            
			echo "ID|TYPE|BUZZER|ALARM|DATA1|DATA2|DATA3|DATA4|DATA5|DATA6|DATA7|DATA8|"."\n";
            //MARTIX 1
			echo  bin2hex($result[$ADDR])."  ".bin2hex($result[$ADDR+1]) ."    ".bin2hex($result[$ADDR+2]) ."    ".bin2hex($result[$ADDR+3])."    ".bin2hex($result[$ADDR+4]) ."    ".bin2hex($result[$ADDR+5])."    ".bin2hex($result[$ADDR+6])."    ".bin2hex($result[$ADDR+7])."    ".bin2hex($result[$ADDR+8])."    ".bin2hex($result[$ADDR+9])."    ".bin2hex($result[$ADDR+10]) ."    ".bin2hex($result[$ADDR+11]) ."\n";
            
	
            break;
			}
			else
			{
			 echo "RX NG....\n";	
		     break;
			}	
			
			
        }
    }
}

socket_close($socket);
echo "Socket Closed\n";

exit();