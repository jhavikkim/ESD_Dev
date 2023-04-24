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
   // echo "checksum = " . dechex($checksum) . "\n";
    return pack("H*", dechex($checksum));
}

function sendCommand($socket, $padding_char, $ch,$rmiid) {
    $data = array_fill(0, 25, $padding_char);
    $data[0] = 'F';
    $data[1] = 'A';
    $data[2] = 'C'; //0x5A ='Z'
	$data[3] = $ch;
	if($rmiid>15){
		$data[4] = pack("H*", dechex($rmiid));
	}else{
		$data[4] = pack("h*", dechex($rmiid));
	}	
	//$data[4] =  pack("h*", dechex($rmiid));  
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

    sendCommand($socket, 'U',$ch,$rmiid);

    socket_close($socket);

    exit();
}