<?php
error_reporting(E_ALL);

function checksum16($data) {
    $checksum = 0;
    for ($i = 0; $i < 24; $i++) {
        $hex = implode(unpack("H*", $data[$i]));
        $c = $checksum;
        $checksum ^= hexdec($hex);
        //echo "c: " . $c . ", hex: " . $hex . ", checksum: " . $checksum . "\n";
    }
    if($checksum>15)
    return pack("H*", dechex($checksum));
    else
	return pack("h*", dechex($checksum));
}

function sendCommand($socket, $padding_char, $cmd , $ch ,$rmgid, $post1,$post2,$post3,$post4,$post5,$post6,$post7,$post8) {
    $data = array_fill(0, 25, $padding_char);
    $data[0] = 'F';
    $data[1] = 'A';
    $data[2] = $cmd;
	$data[3] = $ch;
	if($rmgid>15){
		$data[4] = pack("H*", dechex($rmgid));
	}else{
		$data[4] = pack("h*", dechex($rmgid));
	}//CHx RS485 ID1 如果ID >0X10 要改成=>  pack("H*", 大寫H 
	$data[5] = pack("h*", dechex($post1));    
	$data[6] = pack("h*", dechex($post2));    
	$data[7] = pack("h*", dechex($post3));    
	$data[8] = pack("h*", dechex($post4));    
	$data[9] = pack("h*", dechex($post5));    
	$data[10] = pack("h*", dechex($post6));    
	$data[11] = pack("h*", dechex($post7));    
	$data[12] = pack("h*", dechex($post8));    


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
	$cmd 	= $_GET["cmd"];
    $ch 	= $_GET["ch"];
    $rmgid 	= $_GET["rmgid"];
    $post1 	= $_GET["post1"];
    $post2 	= $_GET["post2"];
    $post3 	= $_GET["post3"];
    $post4 	= $_GET["post4"];
    $post5 	= $_GET["post5"];
    $post6 	= $_GET["post6"];
    $post7 	= $_GET["post7"];
    $post8 	= $_GET["post8"];

    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if ($socket === false) {
        echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
        exit();
    }

    $result = socket_connect($socket, $address, $service_port);
	
	$ADDR = '2';
    if ($result === false) {
        echo "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
        socket_close($socket);
        echo "Socket Closed\n";
        exit();
    } else {
		//echo "Get SCAN ALL CH RS485 ID ANS  hex data: " . bin2hex($result) . "\n";
		//echo "\n";

		//echo "ID|TYPE|BUZZER|ALARM|DATA1|DATA2|DATA3|DATA4|DATA5|DATA6|DATA7|DATA8|"."\n";
		//MARTIX 1
		//echo  bin2hex($result[$ADDR])."  ".bin2hex($result[$ADDR+1]) ."    ".bin2hex($result[$ADDR+2]) ."    ".bin2hex($result[$ADDR+3])."    ".bin2hex($result[$ADDR+4]) ."    ".bin2hex($result[$ADDR+5])."    ".bin2hex($result[$ADDR+6])."    ".bin2hex($result[$ADDR+7])."    ".bin2hex($result[$ADDR+8])."    ".bin2hex($result[$ADDR+9])."    ".bin2hex($result[$ADDR+10]) ."    ".bin2hex($result[$ADDR+11]) ."\n";
			

        echo "Connected.\n";
    }


    sendCommand($socket, 'U', $cmd ,$ch, $rmgid,$post1,$post2,$post3,$post4,$post5,$post6,$post7,$post8);

    socket_close($socket);

    exit();
}