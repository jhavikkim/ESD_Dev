<?php
error_reporting(E_ALL);

function checksum16($data) {
    $checksum = 0;
    for ($i = 0; $i < 24; $i++) {
        $hex = implode(unpack("H*", $data[$i]));
        $c = $checksum;
        $checksum ^= hexdec($hex);
    }
    return pack("H*", dechex($checksum));
}

function sendCommand($socket, $padding_char, $cmd , $ch, $rmiid, $option, $Speed, $Timer) {
	
	$Val1 = "h*";
	$Val2 = "h*";
	$Val3 = "h*";
	if($rmiid > 15){  $Val1 = "H*";	}   //應改改成 > 15 (0x0F) 就轉換 
	if($Speed > 15){  $Val2 = "H*";	}   //應改改成 > 15 (0x0F) 就轉換
	if($Timer > 15){  $Val3 = "H*";	}   //應改改成 > 15 (0x0F) 就轉換
	
    $data = array_fill(0, 25, $padding_char);
    $data[0] = 'F';
    $data[1] = 'A';
    $data[2] = $cmd;
	$data[3] = $ch;
	// $data[4] =  pack($Val1, dechex($rmiid)); //CHx RS485 ID1 如果ID >0X10 要改成=>  pack("H*", 大寫H            
	// $data[5] =  pack("h*", dechex($option)); //OLED 風扇控制 :0x00=POWER_OFF ,0x01=POWER_ON, 0x02=KEEP,0x03=CLEAN 0x06=SET TIME&FAN
	// $data[6] =  pack("h*", dechex(0x00)); //BALANCE_VALUE 平衡值 
	// $data[7] =  pack($Val2, dechex($Speed)); //FAN_SPEED 設定風扇速度  
	// $data[8] =  pack($Val3, dechex($Timer)); //CLEAN_TIMMER 設定清潔時間 
	// $data[9] =  pack("h*", dechex(0x00)); //ALARM_LEFT (READ_ONLY)
	// $data[10] = pack("h*", dechex(0x00)); //ALARM RIGHT (READ_ONLY)
	// $data[11] = pack("h*", dechex(0x00)); //KEEP
	// $data[12] = pack("h*", dechex(0x00)); //KEEP
	

    if($rmiid>15){
        $data[4] =  pack("H*", dechex($rmiid)); //CHx RS485 ID1 如果ID >0X10 要改成=>  pack("H*", 大寫H  
    }else{
        $data[4] =  pack("h*", dechex($rmiid)); //CHx RS485 ID1 如果ID >0X10 要改成=>  pack("H*", 大寫H  
    }

	$data[5] =  pack("h*", dechex($option)); //OLED 風扇控制 :0x00=POWER_OFF ,0x01=POWER_ON, 0x02=KEEP,0x03=CLEAN 0x06=SET TIME&FAN
	$data[6] =  pack("h*", dechex(0x00)); //BALANCE_VALUE 平衡值 
    
    if($Speed>15){
        $data[7] =  pack("H*", dechex($Speed)); //CHx RS485 ID1 如果ID >0X10 要改成=>  pack("H*", 大寫H  
    }else{
        $data[7] =  pack("h*", dechex($Speed)); //CHx RS485 ID1 如果ID >0X10 要改成=>  pack("H*", 大寫H  
    }
    if($Timer>15){
        $data[8] =  pack("H*", dechex($Timer)); //CHx RS485 ID1 如果ID >0X10 要改成=>  pack("H*", 大寫H  
    }else{
        $data[8] =  pack("h*", dechex($Timer)); //CHx RS485 ID1 如果ID >0X10 要改成=>  pack("H*", 大寫H  
    }
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

if($_GET){
    $address = $_GET["rmip"];
    $service_port = $_GET["serviceport"];
    $ch = $_GET["ch"];
    $rmiid = $_GET["rmiid"];
    $option = $_GET["option"];
    $Speed = $_GET["Speed"];
    $Timer = $_GET["Timer"];

    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if ($socket === false) {
        echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
        exit();
    }

    $result = socket_connect($socket, $address, $service_port);
    if ($result === false) {
        echo "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
        socket_close($socket);
        echo "Socket Closed\n";
        exit();
    } else {
        echo "Connected.\n";
    }
	
    //echo "commands sent: " . "U" . "\n" . "Z" . "\n" . $address . "\n" . $ch . "\n" . $rmiid . "\n" . $Speed . "\n" . $Timer . "\n" . $option. "\n";
	
	sendCommand($socket, 'U', 'Z' ,$ch, $rmiid, $option, $Speed, $Timer);

    socket_close($socket);

    exit();
}
