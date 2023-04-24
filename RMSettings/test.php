<?php
function checksum16($data) {
    $checksum = 0;
    for ($i = 0; $i < 24; $i++) {
        $hex = implode(unpack("H*", $data[$i]));
        $c = $checksum;
        $checksum ^= hexdec($hex);
        // echo "c: " . $c . ", hex: " . $hex . ", checksum: " . $checksum . "\n";
    }
    if($checksum>15)
    return pack("H*", dechex($checksum));
    else
	return pack("h*", dechex($checksum));
}



function sendCommand($socket, $cmd , $ch1 ,$rs485_id,$channeldata) {
    $data = array_fill(0, 25, 'U');
    $data[0] = 'F';
    $data[1] = 'A';
    $data[2] = $cmd;
	$data[3] = $ch1;
    $i=0;
	$data[4] =  pack("h*", dechex($rs485_id)); //CHx RS485 ID1 如果ID >0X10 要改成=>  pack("H*", 大寫H 

    for($i=0;$i<8;$i++){
        $data[$i+5] = pack("H*", dechex($channeldata[$i])); //ALARM RESISTANCE CH1 超過設定值 發出ALARM
    } 
	
	// $data[6] =  pack("H*", dechex($channeldata[1])); //ALARM RESISTANCE CH2 超過設定值 發出ALARM
	// $data[7] =  pack("H*", dechex($channeldata[2])); //ALARM RESISTANCE CH3 超過設定值 發出ALARM
	// $data[8] =  pack("H*", dechex($channeldata[3])); //ALARM RESISTANCE CH4 超過設定值 發出ALARM
	// $data[9] =  pack("H*", dechex($channeldata[4])); //ALARM RESISTANCE CH5 超過設定值 發出ALARM
	// $data[10] = pack("H*", dechex($channeldata[5])); //ALARM RESISTANCE CH6 超過設定值 發出ALARM
	// $data[11] = pack("H*", dechex($channeldata[6])); //ALARM RESISTANCE CH7 超過設定值 發出ALARM
	// $data[12] = pack("H*", dechex($channeldata[7])); //ALARM RESISTANCE CH8 超過設定值 發出ALARM
	
    $data[24] =checksum16(implode('', $data));
    $message = implode('', $data);
    echo "Send HEX CMD: " . bin2hex($message) . "\n";

    $result = socket_send($socket, $message, strlen($message), 0);
    if ($result === false) {
        socket_close($socket);
        exit();
    }
}

$address = '192.168.10.123';

$service_port = 4001;

$cmd = 'Z';

$ch1 = '1';

$rs485_id=12;

$channeldata = [0,0,0,30,30,30,30,30];

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) {
    exit();
} 

$result = socket_connect($socket, $address, $service_port);
if ($result === false) {
    socket_close($socket);
    exit();
}

sendCommand($socket, $cmd ,$ch1,$rs485_id,$channeldata);
?>