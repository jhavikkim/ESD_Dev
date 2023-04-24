<?php

function sendDataToRMI($ToUpdate, $cmd, $channel, $rmiid, $fanData){
  $data = array_fill(0, 25, 'U');
  $data[0] = 'F';
  $data[1] = 'A';
  $data[2] = $cmd;  
  $data[3] = $channel;
  if($rmiid>15){
      $data[4] =  pack("H*", dechex($rmiid)); //CHx RS485 ID1 如果ID >0X10 要改成=>  pack("H*", 大寫H  
  }else{
      $data[4] =  pack("h*", dechex($rmiid)); //CHx RS485 ID1 如果ID >0X10 要改成=>  pack("H*", 大寫H  
  }        
  if($ToUpdate){
      for($i=0;$i<count($fanData);$i++){
          if($fanData[$i]>15){
              $data[$i+5] = pack("H*", dechex($fanData[$i]));
          }else{
              $data[$i+5] = pack("h*", dechex($fanData[$i]));
          }
      }
  }
  $data[24] =checksum16(implode('', $data));
  $message = implode('', $data);

  return bin2hex($message);
}

function modifyCleantimeFanspeed($cmd , $ch, $rmiid, $option, $Speed, $Timer) {
  $data = array_fill(0, 25, 'U');
  $data[0] = 'F';
  $data[1] = 'A';
  $data[2] = $cmd;
  $data[3] = $ch;
  if($rmiid>15){
      $data[4] =  pack("H*", dechex($rmiid)); //CHx RS485 ID1 如果ID >0X10 要改成=>  pack("H*", 大寫H  
  }else{
      $data[4] =  pack("h*", dechex($rmiid)); //CHx RS485 ID1 如果ID >0X10 要改成=>  pack("H*", 大寫H  
  }
	$data[5] =  pack("h*", dechex($option)); //OLED 風扇控制 :0x00=POWER_OFF ,0x01=POWER_ON, 0x02=KEEP,0x03=CLEAN 0x06=SET TIME&FAN
	$data[6] =  pack("h*", dechex(0x00)); //BALANCE_VALUE 平衡值 
  
  if($Speed>15){
      $data[7] =  pack("H*", dechex($Speed)); //CHx RS485 ID1 如果ID >0X10 要改成=>  pack("H*", 大寫H  
  }else{
      $data[7] =  pack("h*", dechex($Speed)); //CHx RS485 ID1 如果ID >0X10 要改成=>  pack("H*", 大寫H  
  }
  if($Timer>15){
      $data[8] =  pack("H*", dechex($Timer)); //CHx RS485 ID1 如果ID >0X10 要改成=>  pack("H*", 大寫H  
  }else{
      $data[8] =  pack("h*", dechex($Timer)); //CHx RS485 ID1 如果ID >0X10 要改成=>  pack("H*", 大寫H  
  }
  $data[24] =checksum16(implode('', $data));
  $message = implode('', $data);
 
  return bin2hex($message);
}

// 清除風扇alarm聲音及led燈
$app->post('/cleanAlarmLed', function($request, $response, $args) {
  $r = json_decode($request->getBody());
  $uid = $r->uid;
  $rmIP = $r->rmIP;
  $port = $r->port;
  $rmID = $r->rmID;  
  $portArray = explode('-',$port);
  $ch = $portArray[0];
  $rmiid = $portArray[1];

  $actionCode = sendDataToRMI(false, "C", $ch, $rmiid, "[]"); 
  setRMAction($rmIP, $uid, $rmID, $actionCode, "/RMI-CH/clean_alarm_led.php", $note = '取消硬體警示');

  return $response->withJson($actionCode, 200);
});

$app->post('/setSpeedTimer', function($request, $response, $args) {
  $db = new DbHandler();
  $session = $db->getSession();  
  $r = json_decode($request->getBody());

  $uid = $r->rscuid;
  $port = $r->port;
  $rmIP = $r->rmip;
  $rmID = $r->rmid;
  $Speed = $r->speed;
  $Timer = $r->timer;
  $option = 6;
  $portArray = explode('-',$port);
  $ch = $portArray[0];
  $rmiid = $portArray[1];
  $portLike = $port;

  $result = $db->getQuery(" SELECT gs.rscuid, gs.rmID, gs.port, gs.dtype, fx.timer, fx.speed FROM rm_sensor_combined as gs 
                            LEFT JOIN fan_Except_Table as fx ON fx.rscuid = gs.rscuid WHERE gs.rmID = '{$rmID}' AND gs.port LIKE '{$portLike}' ORDER BY gs.port");

  $returnT = array();
  $returnS = array();
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      array_push($returnS, (int)$row["speed"]);
      array_push($returnT, (int)$row["timer"]);
    }
  }

  $speedtimerUpdate = $db->getUpdate("UPDATE fan_Except_Table SET speed='{$Speed}',timer='{$Timer}', update_date = LOCALTIMESTAMP, update_by = '{$session['name']}'
                                      WHERE rscuid='{$uid}' AND (speed<>'{$Speed}' OR timer<>'{$Timer}')");


  if($Speed != $returnS[0] || $Timer != $returnT[0]){
    $actionCode = modifyCleantimeFanspeed("Z" , $ch, $rmiid, $option, $Speed, $Timer);
    setRMAction($rmIP, $uid, $rmID, $actionCode, "/RMI-CH/set_oled_SpeedTimer.php", $note = '風速或清潔時間設置');
  }
  return $response->withJson($actionCode, 200);

});

// 風扇開關&清潔
$app->post('/setPower', function($request, $response, $args) {
  $db = new DbHandler();
  $session = $db->getSession();
  $r = json_decode($request->getBody());

  $uid = $r->uid;
  $port = $r->port;
  $rmIP = $r->rmIP;
  $rmID = $r->rmID;
  $isfan = $r->isfan;
  $option = $r->option;
  $portArray = explode('-',$port);
  $ch = $portArray[0];
  $rmiid = $portArray[1];

  if ($isfan == 1){
    $portLike = $portArray[0]."-".$portArray[1]."-".'fan%';
  }else if ($isfan == 2){
    $portLike = $portArray[0]."-".$portArray[1]."-".'Dfan%';
  }else if($isfan == 3){
    $portLike = $portArray[0]."-".$portArray[1]."-".'Bar%';
  }
  $update = $db->getUpdate("UPDATE rm_sensor_combined SET `last_value`='{$option}', previous_value = `last_value`, previous_data_time = last_data_time, last_data_time=LOCALTIMESTAMP, update_date =  LOCALTIMESTAMP , update_by = '{$session['name']}' WHERE rscuid='{$uid}' AND (previous_value <> `last_value` OR previous_data_time <> last_data_time OR `last_value` <> '{$option}'); ");
    
  $result = $db->getQuery(" SELECT `last_value` FROM rm_sensor_combined WHERE rmid = '{$rmID}' AND port LIKE '{$portLike}' ORDER BY port");
  
  $return = array();
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
       array_push($return, (int)$row["last_value"]);
    }
  }
  if ($isfan != 3 && ($option == 3 || $option == 1 )) {
    $update = $db->getUpdate("UPDATE fan_except_table SET lastcleandate= LOCALTIMESTAMP, justcleaned='yes', update_date =  LOCALTIMESTAMP , update_by = '{$session['name']}' WHERE rscuid='{$uid}'; ");
  }
  //echo json_encode($return);
  $actionCode = sendDataToRMG(true, "Z", $ch,(int)$rmiid,$return);
  setRMAction($rmIP, $uid, $rmID, $actionCode, "/RMI-CH/set_power_on.php", $note = '控制風扇');
  
  return $response->withJson($actionCode, 200);

});
       
$app->post('/SetNCNO', function($request, $response, $args) {
  $db = new DbHandler();
  $session = $db->getSession();
  $r = json_decode($request->getBody());
  $UID = $r->uid;
  $rmID = $r->rmID;
  $rmIP = $r->rmIP;
  $isNC = $r->isNC;
  $port = $r->port;
  $rmiid = $r->subid;
  $rmType = $r->rmType;
  $portArray = explode('-',$port);
  $ch = $portArray[0];

  if ($rmType == 'RMI-LAN'){

    $updateQuery = "UPDATE fan_except_table SET isnc = $isNC, update_date = LOCALTIMESTAMP, update_by = '{$session['name']}' WHERE rscuid= $UID and isnc <> $isNC ;";
    $update = $db->getUpdate($updateQuery);

    $result = $db->getQuery("SELECT isnc FROM fan_Except_Table WHERE rmid = '{$rmID}' AND rmiid = $rmiid ORDER BY fxuid;");   
    $return = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            array_push($return, (int)$row["isnc"]);
        }
    }
    $actionCode = sendDataToRMG(true, "W", $ch,$rmiid,$return);
    setRMAction($rmIP, $UID, $rmID, $actionCode, "/RMI-CH/set_nc_no.php", $note = 'NC/NO設置');

    return "NC / NO set successfully!";
  }else {
    return "";
  }

});

$app->post('/downloadFanHistoryCSV', function($request, $response, $args) {
  $r = json_decode($request->getBody());
  $startDate = $r->startDate;
  $endDate = date("Y-m-d", strtotime(" 1 day", strtotime($r->endDate)));

  $db = new DbHandler();
  $csv_export = '';

  $result = $db->getQuery("SELECT rsc.rmip as IP_Adress, rsc.dname as Device_Name, rsc.dlocation as Location, fhd.dev_status as Device_Status, fhd.speed as Fan_Speed, fhd.timer as Clean_Time, fhd.create_date  as Data_Time  
  FROM fan_history_data as fhd left JOIN rm_sensor_combined as rsc ON fhd.rscuid = rsc.rscuid
  WHERE fhd.create_date >='$startDate' and fhd.create_date < '$endDate' and fhd.dev_status >= 1 and rsc.isused = 1 ORDER BY fhd.create_date DESC");

  $csv_filename = "FanHistory";

  $csv_export = 'IP Address, Device Name, Location, Fan Speed, Clean Time, Device Status, Data Time';
  $csv_export.= '';
  if ($result->num_rows > 0) {
    $csv_export.= chr(13);
    while ($row = $result->fetch_assoc()) {
        $csv_export.= '"'.$row['IP_Adress'].'",';
        $csv_export.= '"'.$row['Device_Name'].'",';
        $csv_export.= '"'.$row['Location'].'",';
  
        if($row['Fan_Speed']==0){
                  $csv_export.= '"N/A",';
        }else{
          $csv_export.= '"'.$row['Fan_Speed'].'",';
        }
  
        if($row['Clean_Time']==0){
                  $csv_export.= '"N/A",';
        }else{
          $csv_export.= '"'.$row['Clean_Time'].'",';
        }
  
        if($row['Device_Status']==0){
          $csv_export.= '"Off",';
        }else if($row['Device_Status']==1){
          $csv_export.= '"On",';
        }else if($row['Device_Status']==3){
          $csv_export.= '"Clean",';
        }else if($row['Device_Status']==5){
          $csv_export.= '"Alarm",';
        }else{
          $csv_export.= '"'.$row['Device_Status'].'",';
        }
        $csv_export.= '"'.$row['Data_Time'].'",';
        $csv_export.= '';
        $csv_export.= chr(13);
    }
  }

  $csv_export = mb_convert_encoding($csv_export, "big5");

  header('Content-Encoding: UTF-8');
  header("Content-type: text/x-csv; charset=utf-8");
  header("Content-Disposition: attachment; filename=".$csv_filename.".csv");
  
  return $csv_export;
});

$app->post('/downloadBalanceCSV', function($request, $response, $args) {
  $r = json_decode($request->getBody());
  $uid = $r->uid;
  $rmIP = $r->rmIP;
  $port = $r->port;
  $name = $r->name;
  $startDate = $r->startDate;
  $endDate = date("Y-m-d", strtotime(" 1 day", strtotime($r->endDate)));

  $db = new DbHandler();
  $date = date("Ym");
  $predate = date("Ym", strtotime(" -1 month"));

  $csv_filename = $rmIP.'('.$name.')';
  $csv_export = '';

  $result = $db->getQuery(" SELECT rsc.rmip as IP_Adress, rsc.dname as Device_Name, rsc.dlocation as Location, CASE fhd.balance WHEN 1 THEN 'Level 5' when 4 then 'Level 4'  when 3 then 'Level 3' when 2 then 'Level 2' 
                                when 1 then 'Level 1'  when 0 then 'Level 0' when -1 then 'Level -1'  when -2 then 'Level -2'  when -3 then 'Level -3' when -4 then 'Level -4' when -5 then 'Level -5' else 'Others' end as Fan_Balance, fhd.create_date  as Data_Time  
                              FROM fan_history_data as fhd left JOIN rm_sensor_combined as rsc ON fhd.rscuid = rsc.rscuid
                              WHERE fhd.create_date >='$startDate'and fhd.create_date < '$endDate' and rsc.rscuid = $uid ORDER BY rsc.rscuid, fhd.create_date DESC ;");

  $csv_export = 'IP Address, Device Name, Location, Fan Balance, Data Time';   
  $csv_export.= '';
   
  if ($result->num_rows > 0) {
    $csv_export.= chr(13);
    while ($row = $result->fetch_assoc()) {
      $csv_export.= '"'.$row['IP_Adress'].'",';
      $csv_export.= '"'.$row['Device_Name'].'",';
      $csv_export.= '"'.$row['Location'].'",';
      $csv_export.= '"'.$row['Fan_Balance'].'",';
      $csv_export.= '"'.$row['Data_Time'].'",';
      $csv_export.= '';

      $csv_export.= chr(13);
    }
  }

  $csv_export = mb_convert_encoding($csv_export, "big5");

  header('Content-Encoding: UTF-8');
  header("Content-type: text/x-csv; charset=utf-8");
  header("Content-Disposition: attachment; filename=".$csv_filename.".csv");
  
  return $csv_export;

});

$app->post('/fanViewList', function($request, $response, $args) {
  $r = json_decode($request->getBody());
  $return = array();
  $db = new DbHandler();
  $session = $db->getSession();

  $result = $db->getQuery("SELECT DISTINCT rmip, giruid  FROM general_info_rm");
  $ipArray = array();
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $obj = new StdClass();
      $obj->rmIP = $row["rmip"];
      $obj->rmuid = $row["giruid"];
      $obj->status = 0;
      array_push($ipArray, $obj);
    }
  }

  for ($i=0; $i < count($ipArray); $i++) { 
    if (!$socket = @fsockopen($ipArray[$i], 80, $errno, $errstr, 1)){
      $ipArray[$i]->status = 4;
    }else{
      fclose($socket);
    }
  }

  if (!isset($session['uid'])){
    $return["status"] = "錯誤";
    $return["message"] = "未登入狀態";
    return $response->withJson($return, 201);
  }else{
    $auth = $session["auth"];

    $result = $db->getQuery("SELECT rsc.rscuid, rsc.rmid, rsc.rmip, rsc.port, rsc.dname, rsc.model, rsc.dtype, rsc.iszigbee iszigbee, rsc.typename, rsc.unit,
                                  rsc.warning_max, rsc.warning_min, rsc.alert_max, rsc.alert_min, rsc.photo, rsc.location1, rsc.location2, rsc.location3, rsc.dlocation ,
                                  fxt.isnc, rsc.isused, rsc.last_data_time lastdatatime, rsc.last_value lastvalue, rsc.previous_data_time previousdatatime, rsc.previous_value previousvalue, 
                                  fxt.balance, fxt.timer, fxt.speed, rsc.zigbee_signal zigbeesignal, fxt.lastCleanDate lastclean, fxt.nextCleanDate nextclean, 
                                  case when rsc.zigbee_signal > 0 and rsc.zigbee_signal <= 20 then 1 when rsc.zigbee_signal > 20 and rsc.zigbee_signal <= 40 then 2 
                                      when rsc.zigbee_signal > 40 and rsc.zigbee_signal <= 60 then 3 when rsc.zigbee_signal > 60 and rsc.zigbee_signal <= 80 then 4 when rsc.zigbee_signal > 80 then 5 else 0 end as zigbeeSignalLayer
                                FROM rm_sensor_combined rsc LEFT JOIN fan_Except_Table fxt on fxt.rscuid = rsc.rscuid WHERE rsc.dtype in (1, 2, 3) AND rsc.isused = 1 order by rsc.rscuid, rsc.dname; ");

    $return = array();
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {

        $obj = new StdClass();
        $obj->uid = (int)$row["rscuid"];
        $obj->rmID = $row["rmid"];
        $obj->rmIP = $row["rmip"];
        $obj->port = $row["port"];
        $obj->name = $row["dname"];
        $obj->model = $row["model"];
        $obj->isfan = (int)$row["dtype"];
        $obj->isZigbee = (int)$row["iszigbee"];
        $obj->type = $row["typename"];
        $obj->unit = $row["unit"];
        $obj->warning_max = (float)$row["warning_max"];
        $obj->warning_min = (float)$row["warning_min"];
        $obj->alert_max = (float)$row["alert_max"];
        $obj->alert_min = (float)$row["alert_min"];
        $obj->photo = $row["photo"];
        $obj->location1 = $row["location1"];
        $obj->location2 = $row["location2"];
        $obj->location3 = $row["location3"];
        $obj->location = $row["dlocation"];
        $obj->isNC = (int)$row["isnc"];
        $obj->use = (int)$row["isused"];
        $obj->lastDataTime = $row["lastdatatime"];
        $obj->value = (int)$row["lastvalue"];
        $obj->previousDataTime = $row["previousdatatime"];
        $obj->previousValue = (float)$row["previousvalue"];
        $obj->Balance = (int)$row["balance"];
        $obj->Timer = (int)$row["timer"];
        $obj->Speed = (int)$row["speed"];
        $obj->zigbeeSignal = (int)$row["zigbeesignal"];
        if ($obj->isfan == 3 ){
          $obj->LastClean = "";
          $obj->NextClean = "";
        }else{
          $obj->LastClean = $row["lastclean"];
          $obj->NextClean = $row["nextclean"];
        }
        $obj->zigbeeSignalLayer = (int)$row["zigbeeSignalLayer"];
        
        for ($i=0; $i < count($ipArray); $i++) { 
          if($ipArray[$i]->rmIP==$obj->rmIP){
            if($ipArray[$i]->status==4){
              $obj->status = 4;
            }else{
              if(between($obj->warning_min, $obj->warning_max, $obj->value)){
                $obj->status = 1;
              }else if(between($obj->alert_min, $obj->alert_max, $obj->value)){
                $obj->status = 2;
              }else{
                $obj->status = 3;
              }
            }
          }
        }

        array_push($return, $obj);
      }
    }
    return $response->withJson($return, 200);

    // id 資料庫索引 (數字, 流水號)
    // name 使用者為此設備取名 (文字)
    // model 設備型號 (文字)
    // isfan 是否為靜電風扇設備 (true=靜電風扇, false=非靜電風扇)
    // type 設備類型 (fan=靜電風扇, temp=溫度感測, ground=接地感測, esd=靜電感測)
    // value 感測器數值 (感測器：數字, 正負, 小數一位數) (靜電風扇：0=關閉, 1=啟動, 2=清潔)
    // unit 數值單位 (文字)
    // status 設備狀態 (1=正常, 2=警示, 3=異常, 4=離線)
    // use 設備隱藏 (0=隱藏, 1=顯示)
    // warning 警示值範圍 (max-min：數字, 正負)
    // alert 異常值範圍 (max-min：數字, 正負)
    // photo 設備照片路徑 (文字)
    // location1 設備地點(文字, 範圍大)
    // location2 設備地點(文字, 範圍中)
    // location3 設備地點(文字, 範圍小)
    // location 設備地點(文字, 大中小串接)

    // return $response->withJson($return, 200);
  }
});

$app->post('/fanlocationList', function($request, $response, $args) {
  $r = json_decode($request->getBody());
  $return = array();
  $db = new DbHandler();
  $session = $db->getSession();
  if (!isset($session['uid'])){
    $return["status"] = "錯誤";
    $return["message"] = "未登入狀態";
    return $response->withJson($return, 201);
  }else{
    $auth = $session["auth"];
    $result = $db->getQuery("SELECT DISTINCT dlocation FROM rm_sensor_combined WHERE dtype in (1, 2, 3) AND isused = 1");
    $return = array();
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        array_push($return, $row["dlocation"]);
      }
    }
    return $response->withJson($return, 200);
  }
});

$app->post('/balanceRange', function($request, $response, $args) {
  $r = json_decode($request->getBody());
  $return = array();
  $db = new DbHandler();
  $session = $db->getSession();
  $uid = $r->uid;
  $startDate = $r->startDate;
  $endDate = date("Y-m-d", strtotime(" 1 day", strtotime($r->endDate)));

  if (!isset($session['uid'])){
    $return["status"] = "錯誤";
    $return["message"] = "未登入狀態";
    return $response->withJson($return, 201);
  }else{
    $auth = $session["auth"];

    $result = $db->getQuery("SELECT create_date, balance FROM fan_history_data WHERE create_date>='$startDate' AND create_date < '$endDate'  AND rscuid = '$uid' ORDER BY create_date");  
    $return = new StdClass();
    $return->time = array();
    $return->value = array();
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        array_push($return->time, $row["create_date"]);
        array_push($return->value, (int)$row["balance"]);
      }
    }
    return $response->withJson($return, 200);
  }
});

?>