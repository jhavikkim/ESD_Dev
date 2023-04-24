<?php

function getSensorMaxAlert($db) {
  // $result = $db->getOneRecord("SELECT MAX(status) as status FROM current_value_sensor 
  //                                 left join esd.general_info_sensor on general_info_sensor.uid = current_value_sensor.uid WHERE (status = 2 OR status = 3) and  `use` = 1 and pause = 0 ");
  $result = $db->getOneRecord("SELECT MAX(dev_status) as status FROM rm_sensor_combined  WHERE (dev_status = 2 OR dev_status = 3) and  isUsed = 1 and isPaused = 0 ");

  return $result['status'];
}

$app->get('/setLedOn', function($request, $response, $args) {
  ledRun("1", true);
});
$app->get('/setLedOff', function($request, $response, $args) {
  ledRun("1", false);
});

// 清除sensor alarm聲音
$app->post('/setAlarmBuzzer', function($request, $response, $args) {
  $r = json_decode($request->getBody());
  $uid = $r->uid;
  $rmID = $r->rmID;
  $rmIP = $r->rmIP;

  setRMAction($rmIP, $uid, $rmID, "", "/RM/setAlarmBuzzer.php", $note ="取消硬體警示");
  
});

$app->get('/getAlarm', function($request, $response, $args) {
  $db = new DbHandler();
  $useSoundAlarm = getSysSet('useSoundAlarm') == "1";
  $useLedAlarm = getSysSet('useLedAlarm') == "1";
  $statusLed = getSysSet('statusLed');
  
  $status = getSensorMaxAlert($db);
  
  setSysSet('sensorStatus', $status);

  $statusSoundAlarm = trim(getSysSet('statusSoundAlarm'));
  echo $statusSoundAlarm . "< br>"; 
  echo $status; 
  if($useSoundAlarm && ($status==2 || $status==3)){
     echo "ON";
     
    if($statusSoundAlarm==''){
      $playPid = playsound();
      setSysSet('statusSoundAlarm', 1);
    }
  }else{
    if($statusSoundAlarm!=''){
      shell_exec("taskkill /f /im playwav.exe");
      echo "OFF";
      setSysSet('statusSoundAlarm', '');
    }
  }

  if($useLedAlarm && ($status==2 || $status==3)){
    if($status==2 && $statusLed!=$status){
      echo "warning";
      ledRun("2", true);
    }
    if($status==3 && $statusLed!=$status){
      echo "alarm";
      ledRun("1", true);
    }
  }else{
    if(($status!=2 && $status!=3) && $statusLed!=$status){
      echo "OK";
      ledRun("1", false);
    }
  }

  setSysSet('statusLed', $status);
});
?>