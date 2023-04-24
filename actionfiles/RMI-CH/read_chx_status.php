<?php
error_reporting(E_ALL);

function checksum16($data) {
    $checksum = 0;
    for ($i = 0; $i < 24; $i++) {
        $hex = implode(unpack("H*", $data[$i]));
        $c = $checksum;
        $checksum ^= hexdec($hex);
     //    echo "c: " . $c . ", hex: " . $hex . ", checksum: " . $checksum . "\n";
    }
    echo "checksum = " . dechex($checksum) . "\n";
    return pack("H*", dechex($checksum));
}

function sendCommand($socket, $padding_char, $cmd , $ch) {
    $data = array_fill(0, 25, $padding_char);
    $data[0] = 'F';
    $data[1] = 'A';
    $data[2] = $cmd; //0x54 ='T'
	$data[3] = $ch;
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

echo "Read CHx STATUS...\n";

//$address = isset($argv[1])? $argv[1] : '192.168.10.123';
$address = isset($argv[1])? $argv[1] : '192.168.1.123';

$service_port = isset($argv[2])? (int)$argv[2] : 4001;

$ch1 = '1';
if (isset($argv[3])) {
    $ch1 = $argv[3];
}

$read = true;
if (isset($argv[4])) {
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

sendCommand($socket, 'U', 'T' ,$ch1);

usleep(100);


if ($read) {
	
    while(true) {
        $result = socket_read($socket, 32);
        if ($result === false) {
            echo "socket_read() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
        } else {
            
			echo "Get CHx STATUS  hex data: " . bin2hex($result) . "\n";
			
			if ($result[0] == 'Z' && $result[1] == 'X') {
               
            
            echo "ID1:". bin2hex($result[2])."\n";
            echo "BUZZER:". bin2hex($result[3])."\n";
            echo "FAN1:". bin2hex($result[4])."\n";
			echo "FAN2:". bin2hex($result[5])."\n";
			echo "FAN3:". bin2hex($result[6])."\n";
			echo "FAN4:". bin2hex($result[7])."\n";
			
	//		echo "ID2:". bin2hex($result[8])."\n";
    //        echo "BUZZER:". bin2hex($result[9])."\n";
    //        echo "FAN1:". bin2hex($result[10])."\n";
	//		echo "FAN2:". bin2hex($result[11])."\n";
	//		echo "FAN3:". bin2hex($result[12])."\n";
	//		echo "FAN4:". bin2hex($result[13])."\n";
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