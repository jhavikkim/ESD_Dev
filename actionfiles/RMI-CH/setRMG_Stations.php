<?php
error_reporting(E_ALL);

function checksum16($data) {
    $checksum = 0;
    for ($i = 0; $i < 19; $i++) {
        $hex = implode(unpack("H*", $data[$i]));
        $c = $checksum;
        $checksum ^= hexdec($hex);
    }
    return pack("H*", dechex($checksum));
}

function sendCommand($padding_char, $cmd, $ch, $rmgID, $SData) {
    $data = array_fill(0, 25, $padding_char);    
    $data[0] = 'F';
    $data[1] = 'A';
    $data[2] = $cmd;
    $data[3] = $ch;
    if($rmgID>15){
        $data[4] =  pack("H*", dechex($rmgID)); //CHx RS485 ID1 如果ID >0X10 要改成=>  pack("H*", 大寫H  
    }else{
        $data[4] =  pack("h*", dechex($rmgID)); //CHx RS485 ID1 如果ID >0X10 要改成=>  pack("H*", 大寫H  
    }
    for($i=0;$i<count($SData);$i++){
        if($SData[$i]>15){
            $data[$i+5] = pack("H*", dechex($SData[$i]));
        }else{
            $data[$i+5] = pack("h*", dechex($SData[$i]));
        }
    }    
    $data[24] =checksum16(implode('', $data));
    $message = implode('', $data);

    return bin2hex($message);
}

if($_GET){
    $ch = $_GET['ch'];
    $cmd = $_GET['cmd'];    
    $rmgID = $_GET['rmid'];        

}
//echo $alarm_value_plus;
echo sendCommand('U', $cmd, $ch, $rmgID, $SData);

?>