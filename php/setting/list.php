<?php

// 設定sensor上下限
function setAlarm($rmIP, $port, $alert_max, $alert_min) {
  switch ($port) {
    case "ch1":
      $ch = 1;
      break;
    case "ch2":
      $ch = 2;
      break;
    case "ch3":
      $ch = 3;
      break;
    case "ch4":
      $ch = 4;
      break;
    case "temperature":
      $ch = 5;
      break;
    case "humidity":
      $ch = 6;
      break;
    default:
  }
}

$app->post('/itemList', function($request, $response, $args){
  $db = new DbHandler();

  $session = $db->getSession();
  if (!isset($session['uid'])){
    $return["status"] = "錯誤";
    $return["message"] = "未登入狀態";
    return $response->withJson($return, 201);
  }else{
    $auth = $session["auth"];
    $result = $db->getQuery("SELECT rsc.rscuid, rsc.rmid, rsc.rmip, rsc.port, rsc.dname, rsc.model, rsc.dtype, rsc.iszigbee, rsc.typename, rsc.unit, rsc.warning_max, rsc.warning_min, rsc.alert_max, 
                                  gir.usbrelayid,	rsc.alert_min, rsc.photo, rsc.location1, rsc.location2, rsc.location3, rsc.dlocation, rsc.isused, rsc.ispaused, fxt.isnc, gir.rmtype
                                FROM rm_sensor_combined rsc LEFT JOIN fan_Except_Table fxt on fxt.rscuid = rsc.rscuid LEFT JOIN general_info_rm gir ON rsc.giruid = gir.giruid ; ");

    $return = array();
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $obj = new StdClass();
        $obj->uid = (int)$row["rscuid"];
        $obj->rmID = $row["rmid"];
        $obj->port = $row["port"];
        $obj->name = $row["dname"];
        $obj->model = $row["model"];
        $obj->isfan = (int)$row["dtype"];
        $obj->isZigbee = (int)$row["iszigbee"];
        $obj->type = $row["typename"];
        $obj->unit = $row["unit"];
        $obj->usbrelayid = $row["usbrelayid"];        
        $obj->warning_max = $row["warning_max"];
        $obj->warning_min = $row["warning_min"];
        $obj->alert_max = $row["alert_max"];
        $obj->alert_min = $row["alert_min"];
        $obj->location1 = $row["location1"];
        $obj->location2 = $row["location2"];
        $obj->location3 = $row["location3"];
        $obj->location = $row["dlocation"];
        $obj->isNC = (int)$row["isnc"];
        $obj->use = (int)$row["isused"];
        $obj->pause = (int)$row["ispaused"];
        $obj->rmIP = $row["rmip"];
        $obj->rmType = $row["rmtype"];
        array_push($return, $obj);
      }
    }
    return $response->withJson($return, 200);
  }
});

$app->post('/listContent', function($request, $response, $args){
  $r = json_decode($request->getBody());
  $db = new DbHandler();

  $session = $db->getSession();
  $uid = $r->uid;
  if (!isset($session['uid'])){
    $return["status"] = "錯誤";
    $return["message"] = "未登入狀態";
    return $response->withJson($return, 201);
  }else{
    $auth = $session["auth"];

    $result = $db->getQuery("SELECT rsc.rscuid, rsc.rmid, rsc.rmip, subid, gir.usbrelayid, gir.rmid, rsc.port, rsc.dname, rsc.model, rsc.dtype, rsc.iszigbee, rsc.typename, rsc.unit, 
                                  rsc.warning_max, rsc.warning_min, rsc.alert_max, rsc.alert_min, rsc.photo, rsc.location1, rsc.location2, rsc.location3, rsc.dlocation, 
                                  rsc.isused, rsc.ispaused, gir.rmtype, fxt.isnc, fxt.balance, fxt.timer, fxt.speed, fxt.lastCleanDate lastclean, fxt.nextCleanDate nextclean
                              FROM rm_sensor_combined rsc
                              LEFT JOIN general_info_rm gir ON rsc.giruid = gir.giruid
                              LEFT JOIN fan_Except_Table fxt on fxt.rscuid = rsc.rscuid WHERE rsc.rscuid = '$uid' ;");

    $obj = new StdClass();
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {

        $obj->uid = (int)$row["rscuid"];
        $obj->rmID = $row["rmid"];
        $obj->port = $row["port"];
        $obj->name = $row["dname"];
        $obj->model = $row["model"];
        $obj->isfan = (int)$row["dtype"];
        $obj->isZigbee = (int)$row["iszigbee"];
        $obj->type = $row["typename"];
        $obj->unit = $row["unit"];
        $obj->usbrelayid = $row["usbrelayid"];
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
        $obj->subid = (int)$row["subid"];
        $obj->pause = (int)$row["ispaused"];
        $obj->rmIP = $row["rmip"];
        $obj->rmType = $row["rmtype"];
        $obj->Balance = (int)$row["balance"];
        $obj->Timer = (int)$row["timer"];
        $obj->Speed = (int)$row["speed"];
        if ($obj->isfan == 3 ){
          $obj->LastClean = "";
          $obj->NextClean = "";
        }else{
          $obj->LastClean = $row["lastclean"];
          $obj->NextClean = $row["nextclean"];
        }
      }
    }
    return $response->withJson($obj, 200);
  }
});

// 修改PI和RM的IP
$app->post('/modifyContent', function($request, $response, $args){
  $r = json_decode($request->getBody());
  $db = new DbHandler();

  $session = $db->getSession();
  $uid = $r->uid;
  $model = $r->model;

  if (!isset($session['uid'])){
    $return["status"] = "錯誤";
    $return["message"] = "未登入狀態";
    return $response->withJson($return, 201);
  }else{
    $auth = $session["auth"];

    $rmOriginal = $db->getQuery("SELECT rmIP FROM general_info_rm WHERE rmID <> '$r->rmID'; ");
    $rmOriginalObj = new StdClass();

    if ($rmOriginal->num_rows > 0) {      
      while ($row = $rmOriginal->fetch_assoc()) {         
        if($r->rmIP==$row["rmIP"]){
          echo "rmIP重複"."< br>";
          return "rmIP重複";
        }
      }

      // //此段修改PI和RM的IP
      $Updatetime = date('Y-m-d H:m:s') ; 
      $rmUpdate = $db->getUpdate("UPDATE general_info_rm SET rmIP='$r->rmIP', usbRelayID='$r->usbrelayid', update_date = LOCALTIMESTAMP, update_by = '{$session['name']}'  WHERE rmID='$r->rmID' AND (rmIP <> '$r->rmIP' OR usbRelayID <> '$r->usbrelayid') ; ");
      
      $rmUpdate = $db->getUpdate("UPDATE rm_sensor_combined SET rmIP='$r->rmIP', update_date = LOCALTIMESTAMP, update_by = '{$session['name']}'  WHERE rmID='$r->rmID' AND (rmIP <> '$r->rmIP'); ");
      
      $location = $r->location1.'-'.$r->location2.'-'.$r->location3;
      $sensorUpdate = $db->getUpdate("UPDATE  rm_sensor_combined SET dname='$r->name',warning_max='$r->warning_max', warning_min='$r->warning_min', alert_max='$r->alert_max', alert_min='$r->alert_min', location1='$r->location1', 
                                              location2='$r->location2', location3='$r->location3', dlocation='$location', ispaused ='$r->pause', isused = '$r->use', update_date = LOCALTIMESTAMP, update_by = '{$session['name']}' 
                                              WHERE rscuid = '$r->uid' AND (dname<>'$r->name' OR location1<>'$r->location1' OR location2<>'$r->location2' OR location3<>'$r->location3' OR dlocation<>'$location' OR isused<>'$r->use' OR ispaused <>'$r->pause' 
                                                    OR warning_max<>'$r->warning_max' OR warning_min<>'$r->warning_min' OR alert_max<>'$r->alert_max' OR alert_min<>'$r->alert_min' ); "); 
      
      if ($model != "靜電感測器" && $model != "靜電感測器") {
        $fanExceptUpdate = $db->getUpdate("UPDATE  fan_Except_Table SET isNC = '$r->isNC', update_date = LOCALTIMESTAMP, update_by = '{$session['name']}' WHERE rscuid = '$r->uid' AND isNC <> '$r->isNC'; "); 
      }

    }else{
      //此段修改PI和RM的IP
      $rmUpdate = $db->getUpdate("UPDATE general_info_rm SET rmIP='$r->rmIP', usbRelayID='$r->usbrelayid', update_date = LOCALTIMESTAMP, update_by = '{$session['name']}'  WHERE rmID='$r->rmID' AND (rmIP <> '$r->rmIP' OR usbRelayID <> '$r->usbrelayid') ; ");
      
      $rmUpdate = $db->getUpdate("UPDATE rm_sensor_combined SET rmIP='$r->rmIP', update_date = LOCALTIMESTAMP, update_by = '{$session['name']}'  WHERE rmID='$r->rmID' AND (rmIP <> '$r->rmIP'); ");
      $location = $r->location1.'-'.$r->location2.'-'.$r->location3;
      $sensorUpdate = $db->getUpdate("UPDATE rm_sensor_combined SET dname='$r->name', warning_max='$r->warning_max', warning_min='$r->warning_min', alert_max='$r->alert_max', alert_min='$r->alert_min', location1='$r->location1', 
                                              location2='$r->location2', location3='$r->location3', dlocation='$location', ispaused ='$r->pause', isused='$r->use', update_date = LOCALTIMESTAMP, update_by = '{$session['name']}' 
                                          WHERE rscuid = '$r->uid' AND (dname<>'$r->name' OR location1<>'$r->location1' OR location2<>'$r->location2' OR location3<>'$r->location3' OR dlocation<>'$location' OR isused<>'$r->use' OR ispaused <>'$r->pause'
		                                          OR warning_max<>'$r->warning_max' OR warning_min<>'$r->warning_min' OR alert_max<>'$r->alert_max' OR alert_min<>'$r->alert_min'); ");     
      
      if ($model != "靜電感測器" && $model != "靜電感測器") {
        $fanExceptUpdate = $db->getUpdate("UPDATE  fan_Except_Table SET isNC = '$r->isNC', update_date = LOCALTIMESTAMP, update_by = '{$session['name']}' WHERE rscuid = '$r->uid' AND isNC <> '$r->isNC'; "); 
      }
    }

    if ($r->model!="靜電除塵風扇"){
      setAlarm($r->rmIP, $r->port, $r->alert_max, $r->alert_min);
    }

    $result = $db->getQuery("SELECT rsc.rscuid, rsc.rmid, rsc.rmip, rsc.port, rsc.dname, rsc.model, rsc.dtype, rsc.iszigbee, rsc.typename, rsc.unit, rsc.warning_max, rsc.warning_min, rsc.alert_max, 
                                    rsc.alert_min, rsc.photo, rsc.location1, rsc.location2, rsc.location3, rsc.dlocation, rsc.isused, rsc.ispaused, fxt.isnc, gir.rmtype
                              FROM rm_sensor_combined rsc LEFT JOIN fan_Except_Table fxt on fxt.rscuid = rsc.rscuid LEFT JOIN general_info_rm gir ON fxt.rmid = gir.rmid and rsc.giruid = gir.giruid ; ");

    $return = array();
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $obj = new StdClass();
        $obj->uid = (int)$row["rscuid"];
        $obj->rmID = $row["rmid"];
        $obj->port = $row["port"];
        $obj->name = $row["dname"];
        $obj->model = $row["model"];
        $obj->isfan = (int)$row["dtype"];
        $obj->isZigbee = (int)$row["iszigbee"];
        $obj->type = $row["typename"];
        $obj->unit = $row["unit"];
        $obj->warning_max = $row["warning_max"];
        $obj->warning_min = $row["warning_min"];
        $obj->alert_max = $row["alert_max"];
        $obj->alert_min = $row["alert_min"];
        $obj->location1 = $row["location1"];
        $obj->location2 = $row["location2"];
        $obj->location3 = $row["location3"];
        $obj->location = $row["dlocation"];
        $obj->isNC = (int)$row["isnc"];
        $obj->use = (int)$row["isused"];
        $obj->pause = (int)$row["ispaused"];
        $obj->rmIP = $row["rmip"];
        $obj->rmType = $row["rmtype"];
        array_push($return, $obj);
      }
    }
    return $response->withJson($return, 200);
  }
});

// Digital fan Settings
$app->post('/modifyDFanContent', function($request, $response, $args){
  $r = json_decode($request->getBody());
  $db = new DbHandler();
  $uid = $r->uid;
  $port = $r->port;
  $rmIP = $r->rmIP;
  $rmID = $r->rmID;
  $name = $r->name;
  $Speed = $r->Speed;
  $Timer = $r->Timer;
  $pause = $r->pause;
  $isfan = 1;

  $use = $r->use;
  $option = 6;
  $portArray = explode('-',$port);
  $ch = $portArray[0];
  $rmiid = $portArray[1];
  $portLike = $port;

  $session = $db->getSession();
  if (!isset($session['uid'])){
    $return["status"] = "錯誤";
    $return["message"] = "未登入狀態";
    return $response->withJson($return, 201);
  }else{
    $auth = $session["auth"];

    //此段修改RM的IP
    $location = $r->location1.'-'.$r->location2.'-'.$r->location3;    
    $sensorUpdate = $db->getUpdate("UPDATE rm_sensor_combined SET dname='$name', location1='$r->location1', location2='$r->location2', location3='$r->location3', dlocation='$location', ispaused='$pause', isused = '$use', update_date = LOCALTIMESTAMP, update_by = '{$session['name']}'
                                    WHERE rscuid='$uid' AND (dname<>'$name' OR location1<>'$r->location1' OR location2<>'$r->location2' OR location3<>'$r->location3' OR dlocation<>'$location' OR isused <> '$use' OR ispaused<>'$pause'); "); 
    
    $result = $db->getQuery("SELECT rsc.rscuid, rsc.rmid, rsc.rmip, rsc.dtype, rsc.iszigbee, fxt.balance, fxt.timer, fxt.speed FROM rm_sensor_combined rsc 
                                LEFT JOIN fan_Except_Table fxt on fxt.rscuid = rsc.rscuid 
                                LEFT JOIN general_info_rm gir ON fxt.rmid = gir.rmid and rsc.giruid = gir.giruid WHERE rsc.rscuid = '$uid' ; ");
    $return = array();

    $num = $result->num_rows;
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $obj = new StdClass();
        $isfan = $row["dtype"];
        $oldSpeed = (int)$row["speed"];
        $oldTime = (int)$row["timer"];
      }
    }

    $fanUpdate = $db->getUpdate("UPDATE fan_Except_Table SET timer='$Timer', speed='$Speed', update_date = LOCALTIMESTAMP, update_by = '{$session['name']}' WHERE rscuid='$uid' AND (speed<>'{$Speed}' OR timer<>'{$Timer}'); "); 

    $actionCode = "";

    if ($isfan == 2 && ($Speed != $oldSpeed || $Timer != $oldTime)){
      $actionCode = modifyCleantimeFanspeed("Z" , $ch, $rmiid, $option, $Speed, $Timer);
      setRMAction($rmIP, $uid, $rmID, $actionCode, "/RMI-CH/set_oled_SpeedTimer.php", $note = '風速或清潔時間設置');
    }
    
    return $response->withJson($actionCode, 200);
  }
});


// 新增RM
$app->post('/addRM', function($request, $response, $args){
  $r = json_decode($request->getBody());
  $db = new DbHandler();

  $session = $db->getSession();
  $now = date("YmdHis");
  $rmID = $r->rmType.'-'.$now;
  $rmType = $r->rmType;
  $rmIP = $r->rmIP;
  $giruid = '0';
  $ch = $r->ch;
  $deviceID = $r->deviceID;
  $use = $r->use;
  $isZigbee = $r->isZigbee;
  $subID = 0;
  $isfan = 0;
  $photo = "";
  $deviceNumber = 0;
  $port = $ch."-".$deviceID."-".$r->device;
  $unit = "";
  $warning_max = 0;
  $warning_min = 0;
  $alert_max = 0;
  $alert_min = 0;
  $isDuplicate = false;


  $result = $db->getQuery("SELECT rmid, giruid FROM general_info_rm where rmIP = '$rmIP'; ");   
 
  if ($result->num_rows > 0 && $rmType == 'RMI-C-H') {    
    while ($row = $result->fetch_assoc()) {
      $rmID = $row["rmid"];
      $giruid = (int)$row["giruid"];
    }
  }else if ( $result->num_rows > 0 && ($rmType == 'RM' || $rmType == 'RMI-LAN')) {
    $isDuplicate = true;
  }else {    
    $db->getInsert("INSERT INTO general_info_rm (rmID, rmType, rmIP) VALUES ('$rmID', '$rmType', '$rmIP'); ");
    
    $result = $db->getQuery("SELECT rmid, giruid FROM general_info_rm where rmIP = '$rmIP'");

    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $rmID = $row["rmid"];
        $giruid = (int)$row["giruid"];
      }
    }
  }

  // 判斷RMType
  if ($rmType=='RMI-C-H'){
    //依據device的類別新增資料到general_info_sensor,current_value_sensor,notice_table
    switch($r->device){
      case "bar":
        $isfan = 3;
        $subID = $deviceID;
        $photo = "images/equip/bar.png";
        $model = "靜電除塵棒";
        $deviceNumber = 4;
        $name = "bar";
        $typename = "Ionizer";
        break;
      case "fan":
        $isfan = 1;
        $subID = $deviceID;
        $photo = "images/equip/fan.png";
        $model = "靜電除塵風扇";
        $deviceNumber = 4;
        $name = "fan";
        $typename = "Ionizer";
        break;
      case "Dfan":
        $isfan = 2;
        $subID = $deviceID;
        $model = "靜電除塵風扇";
        $deviceNumber = 1;
        $name = "Dfan";
        $typename = "Ionizer";

        break;
      case "rmg":
        $isfan = -1;
        $subID = $deviceID;
        $model = "接地電阻感測器";
        $deviceNumber = 8;
        $alert_max = 12;
        $unit = "Ω";
        $typename = "Grounding";
        $name = "GND";

        $TrmID = str_replace('-','_', $rmID);
        
        $createTable = $db->getQuery("CREATE TABLE IF NOT EXISTS current_history_rmg_{$TrmID}_{$deviceID} (
                                        hguid INT NOT NULL AUTO_INCREMENT,
                                        rmgid INT NOT NULL,
                                        rmIP VARCHAR(50) NOT NULL,
                                        data1 decimal NOT NULL DEFAULT 0.00,
                                        data2 decimal NOT NULL DEFAULT 0.00,
                                        data3 decimal NOT NULL DEFAULT 0.00,
                                        data4 decimal NOT NULL DEFAULT 0.00,
                                        data5 decimal NOT NULL DEFAULT 0.00,
                                        data6 decimal NOT NULL DEFAULT 0.00,
                                        data7 decimal NOT NULL DEFAULT 0.00,
                                        data8 decimal NOT NULL DEFAULT 0.00,
                                        create_date TIMESTAMP NULL DEFAULT LOCALTIMESTAMP,
                                        create_by varchar(50) COLLATE utf8_unicode_ci DEFAULT 'admin',
                                        PRIMARY KEY (hguid),
                                        KEY idx_hrmg{$deviceID}_hguid (hguid) USING BTREE,
                                        KEY idx_hrmg{$deviceID}_create_date (create_date) USING BTREE,
                                        KEY idx_hrmg{$deviceID}_rmgid (rmgid) USING BTREE
                                        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; ");
        break;
    }
  }
  else{

    if ($isDuplicate) {
      return "rmIP duplicate";
    }

    $isZigbee = 0;
    $model = "靜電感測器";
    $deviceNumber = 4;

    if($rmType=='RMI-LAN'){
      $isfan = 1;
      $subID = 0;
      $photo = "images/equip/fan.png";
      $model = "靜電除塵風扇";
      $deviceID = 0;
      $typename = "Ionizer";
      $port = "0-0-Fan";
      $deviceNumber = 4;
      $name = "fan";
    }else {
      $isfan = 0;
      $port = "ch"; 
      $unit = "V";
      $name = "ESD-00";
      $typename = "RM1-ESD";
      $deviceNumber = 4;
      $warning_max = 1000;
      $warning_min = -1000;
      $alert_max = 2000;
      $alert_min = -2000;
    }
  }

  for($i=1;$i<=$deviceNumber;$i++){

    $db->getInsert("INSERT INTO rm_sensor_combined (giruid, rmID, rmIP, port, subID, model, typename, dtype, isZigbee, dname, photo, isUsed, isPaused, unit, warning_max, warning_min, alert_max, alert_min, location1, location2, location3, dlocation) 
                    VALUES ('$giruid', '$rmID', '$rmIP', '".$port.$i."', '$subID', '$model', '$typename', '$isfan', $isZigbee, '".$name.$i."', '$photo', 0, 0, '$unit' ,$warning_max,$warning_min,$alert_max,$alert_min,'','','',''); ");
    $db->getInsert("INSERT INTO notice_table (rmID, port, dtype) VALUES ('$rmID', '".$port.$i."', '$isfan'); ");
        
    if($isfan != 0){  
    
      $result = $db->getQuery("SELECT rscuid, rmid FROM rm_sensor_combined where giruid = '$giruid' and port = '".$port.$i."'; "); 
      
      if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          $rscuid = $row["rscuid"];
          if($isfan == -1){      
            $db->getInsert("INSERT INTO rmg_Except_Table (rscuid, rmID, rmgID) VALUES ('$rscuid', '$rmID', '$subID'); ");
          }else if ($isfan >= 1 ){
            $db->getInsert("INSERT INTO fan_Except_Table (rscuid, rmID, rmiid) VALUES ('$rscuid', '$rmID', '$subID'); ");
          }
        }
      }
    }
  }

  if($r->device=='rm2'){

    $db->getInsert("INSERT INTO rm_sensor_combined (giruid, rmID, rmIP, port, model, typename, dtype, photo, isUsed, isPaused, unit, dname, warning_max, warning_min, alert_max, alert_min, location1, location2, location3, dlocation) 
                    VALUES ('$giruid', '$rmID', '$rmIP', 'temp', '溫度感測器', 'RM2-temperature', '$isfan', '$photo', 0, 0, '°C', 'temp', '40', '22', '50', '20','','','',''); ");
    $db->getInsert("INSERT INTO rm_sensor_combined (giruid, rmID, rmIP, port, model, typename, dtype, photo, isUsed, isPaused, unit, dname, warning_max, warning_min, alert_max, alert_min, location1, location2, location3, dlocation) 
                    VALUES ('$giruid', '$rmID', '$rmIP', 'rh', '濕度感測器', 'RM2-Humidity', '$isfan', '$photo', 0, 0, '%', 'Hum', '58', '48', '70', '40','','','',''); ");
  
    $db->getInsert("INSERT INTO notice_table (rmID, port, dtype) VALUES ('$rmID', 'temp', '$isfan'); ");
    $db->getInsert("INSERT INTO notice_table (rmID, port, dtype) VALUES ('$rmID', 'rh', '$isfan'); ");
  }

  return $rmID;

});

?>