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

function sendCommand($socket, $padding_char, $cmd , $ch, $rmiid, $Option) {
    $data = array_fill(0, 25, $padding_char);
    $data[0] = 'F';
    $data[1] = 'A';
    $data[2] = $cmd;
	$data[3] = $ch;
	$data[4] =  pack("h*", dechex($rmiid)); //CHx RS485 ID1 如果ID >0X10 要改成=>  pack("H*", 大寫H            
	$data[5] =  pack("h*", dechex($Option)); //OLED 風扇控制 :0x00=POWER_OFF ,0x01=POWER_ON, 0x02=KEEP,0x03=CLEAN 0x06=SET TIME&FAN
	$data[6] =  pack("h*", dechex(0x00)); //BALANCE_VALUE 平衡值 (READ_ONLY)
	$data[7] =  pack("H*", dechex(0x00)); //FAN_SPEED 設定風扇速度  EX:30% =30=0x1E
	$data[8] =  pack("H*", dechex(0x00)); //CLEAN_TIMMER 設定清潔時間 EX:32小時清潔一次=32=0x20
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
    $Option = $_GET["Option"];

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
	
    sendCommand($socket, 'U', 'Z' ,$ch, $rmiid, $Option);

    socket_close($socket);

    exit();
}