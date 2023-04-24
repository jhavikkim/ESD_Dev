<?php
error_reporting(E_ALL);

function checksum16($data) {
    $checksum = 0;
    for ($i = 0; $i < 19; $i++) {
        $hex = implode(unpack("H*", $data[$i]));
        $c = $checksum;
        $checksum ^= hexdec($hex);
    //     echo "c: " . $c . ", hex: " . $hex . ", checksum: " . $checksum . "\n";
    }
    // echo "checksum = " . dechex($checksum) . "\n";
    return pack("H*", dechex($checksum));
}

function sendCommand($padding_char, $ch,$alarm_value_plus,$alarm_value_minus) {
    $data = array_fill(0, 20, $padding_char);
    $data[0] = '5';
    $data[1] = '1';
    $data[2] = $ch; // 支持通道1, 2, 3, 4
	$data[3] = '+';
	$data[4] = $alarm_value_plus[0];
	$data[5] = $alarm_value_plus[2];
	$data[6] = $alarm_value_plus[3];
	$data[7] = $alarm_value_plus[4];
	$data[8] = '-';
	$data[9] = $alarm_value_minus[0]; 
	$data[10] = $alarm_value_minus[2];
	$data[11] = $alarm_value_minus[3];
	$data[12] = $alarm_value_minus[4];
	
	
	echo " Given dada array \n";
	print_r ($data); 
    $data[19] = checksum16(implode('', $data));
    $message = implode('', $data);

    return bin2hex($message);
}

$ch = '1' + 4; // ch1
if (isset($_GET['ch'])) {
    $ch = $_GET['ch'] + 4; // 1~4
}

$alarm_value_plus = '0.600';
if (isset($_GET['max'])) {
    $alarm_value_plus = sprintf("%1.3f", $_GET['max']/1000);
}

$alarm_value_minus = '0.600';
if (isset($_GET['min'])) {
    $alarm_value_minus = sprintf("%1.3f", abs($_GET['min'])/1000);
}

//echo $alarm_value_plus;
echo sendCommand('U', $ch,$alarm_value_plus,$alarm_value_minus);
?>