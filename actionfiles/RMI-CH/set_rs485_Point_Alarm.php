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
    //echo "checksum = " . dechex($checksum) . "\n";
    return pack("H*", dechex($checksum));
}

function sendCommand($socket, $padding_char, $cmd , $ch ,$rmiid,$point1,$point2,$point3,$point4,$point5,$point6,$point7,$point8 ) {
    $data = array_fill(0, 25, $padding_char);
    $data[0] = 'F';
    $data[1] = 'A';
    $data[2] = $cmd;
	$data[3] = $ch1;
	if($rmiid>15){
		$data[4] =  pack("H*", dechex($rmiid)); //CHx RS485 ID1 如果ID >0X10 要改成=>  pack("H*", 大寫H  
	}else{
		$data[4] =  pack("h*", dechex($rmiid)); //CHx RS485 ID1 如果ID >0X10 要改成=>  pack("H*", 大寫H  
	}              
	$data[5] =  pack("h*", dechex($point1)); //POINT1 ALARM OFF
	$data[6] =  pack("h*", dechex($point2)); //POINT2 ALARM OFF
	$data[7] =  pack("h*", dechex($point3)); //POINT3 ALARM OFF
	$data[8] =  pack("h*", dechex($point4)); //POINT4 ALARM OFF
	$data[9] =  pack("h*", dechex($point5)); //POINT5 ALARM ON
	$data[10] = pack("h*", dechex($point6)); //POINT6 ALARM ON
	$data[11] = pack("h*", dechex($point7)); //POINT7 ALARM ON
	$data[12] = pack("h*", dechex($point8)); //POINT8 ALARM ON
	
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
	$point1 = $_GET["point1"];
	$point2 = $_GET["point2"];
	$point3 = $_GET["point3"];
	$point4 = $_GET["point4"];
	$point5 = $_GET["point5"];
	$point6 = $_GET["point6"];
	$point7 = $_GET["point7"];
	$point8 = $_GET["point8"];

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

    sendCommand($socket, 'U', 'E' ,$ch,$rmiid,$point1,$point2,$point3,$point4,$point5,$point6,$point7,$point8);

    socket_close($socket);

    exit();
}
