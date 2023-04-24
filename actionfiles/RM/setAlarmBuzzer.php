<?php

function checksum16($data) {
    $checksum = 0;
    for ($i = 0; $i < 19; $i++) {
        $hex = implode(unpack("H*", $data[$i]));
        $c = $checksum;
        $checksum ^= hexdec($hex);
    //     echo "c: " . $c . ", hex: " . $hex . ", checksum: " . $checksum . "\n";
    }
    //echo "checksum = " . dechex($checksum) . "\n";
    return pack("H*", dechex($checksum));
}

function sendCommand($padding_char, $cmd) {
    $data = array_fill(0, 20, $padding_char);
    $data[0] = '5';
    $data[1] = '1';
    $data[2] = $cmd;
    $data[19] =checksum16(implode('', $data));
    $message = implode('', $data);

    return bin2hex($message);
}   

//收到後立刻下指令
echo sendCommand( 'U', 'C');
?>