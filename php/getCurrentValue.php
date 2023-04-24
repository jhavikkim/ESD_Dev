<?php

function getRmType($db, $rmID) {
  $result = $db->getOneRecord("SELECT rmid, rmtype, rmip, offline, update_date FROM general_info_rm WHERE rmID = '{$rmID}'; ");
  return $result;
}

function getCurrentData($db, $rmID, $port) {
  $result = $db->getOneRecord("SELECT rscuid, last_data_time, last_value, ispaused, dname FROM rm_sensor_combined WHERE rmID = '{$rmID}' AND port = '{$port}' order by rscuid ");
  return $result;
}

function setCurrentData($db, $previousValue, $previousDataTime, $Balance,$Timer, $Speed, $lastValue, $uid, $status) {
  if($status==4){
    $update = $db->getUpdate(" UPDATE rm_sensor_combined SET dev_status='{$status}', update_date = LOCALTIMESTAMP, update_by = 'admin' WHERE rscuid='{$uid}' AND dev_status<>'{$status}'; ");
  }else{
    $update = $db->getUpdate(" UPDATE rm_sensor_combined SET previous_value='{$previousValue}', previous_data_time='{$previousDataTime}', last_value='{$lastValue}',
                                      last_data_time=CURRENT_TIMESTAMP, dev_status='{$status}', update_date = LOCALTIMESTAMP, update_by = 'admin' 
                                    WHERE rscuid='{$uid}' AND (previous_value<>'{$previousValue}' OR last_value<>'{$lastValue}'); ");
  }
}

function getSensorInfo($db, $rmID, $port) {
  $result = $db->getOneRecord("SELECT * FROM general_info_sensor WHERE rmID = '{$rmID}' AND port = '{$port}'");
  return $result;
}

function checkHistoryTable($db, $uid, $date) {
  $result = $db->getQuery("SHOW TABLES LIKE 'current_history_data_{$uid}_{$date}'");
  $exist = $result->num_rows>0;
  return $exist;
}

function createHistoryTable($db, $uid, $date) {
  $createTable = $db->getQuery("CREATE TABLE `current_history_data_{$uid}_{$date}` (
    `hrmuid` int NOT NULL AUTO_INCREMENT, 
    `rmIP` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
    `sensor` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
    `value` decimal(10,2) DEFAULT '0.00', 
    `status` int(11) DEFAULT '1', 
    `create_date` TIMESTAMP NULL DEFAULT LOCALTIMESTAMP,
    `create_by` varchar(50) NULL DEFAULT 'admin',
    PRIMARY KEY (`hrmuid`),
    KEY idx_hrm{$uid}_hrmuid (hrmuid) USING BTREE,
    KEY idx_hrm{$uid}_status (status) USING BTREE,
    KEY idx_hrm{$uid}_create_date (create_date) USING BTREE,
    KEY idx_hrm{$uid}_status_Date (status, create_date)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
}

function insertHistoryTable($db, $uid,$rmIP, $name, $date, $value, $status) {
  $useOverwrite = (getSysSet("useOverwrite")=="1");
  $hdlimit = (getSysSet("hdlimit")=="")?10.0:floatval(getSysSet("hdlimit"));
  $bytes = disk_free_space(".");
  $hdGB = $bytes / 1024 / 1024 / 1024;
  if($useOverwrite && ($hdGB < $hdlimit)){
    deleteLastOneFull();
  }
  
  $insertData = $db->getInsert("INSERT INTO current_history_data_{$uid}_{$date} (rmIP, sensor, value, status)
                                VALUES ('{$rmIP}', '{$name}', '{$value}', '{$status}');");

}

function setSensorAlert($db, $rmID, $port, $status) {
  $result = $db->getOneRecord("SELECT noticeuid, datatime, dev_status, notice_read FROM notice_table WHERE rmID = '{$rmID}' AND port = '{$port}' ");
  $uid = $result['uid'];
  if ($status>=$result['status']){
    $update = $db->getUpdate("UPDATE notice_table SET dev_status='{$status}', datatime=LOCALTIMESTAMP, update_date = LOCALTIMESTAMP, update_by = 'admin' WHERE noticeuid='{$uid}' AND dev_status<>'{$status}'; ");
  }
}

function rmOnline($ip) {
  if (!$socket = @fsockopen($ip, 80, $errno, $errstr, 30)){
    // echo "Offline!";
    return false;
  }else{
    // echo "Online!";
    return true; 
    fclose($socket);
  }
}

$app->get('/ping', function($request, $response, $args) {

  if (!$socket = @fsockopen("192.168.1.244", 80, $errno, $errstr, 1)){
    echo "Offline!";
    fclose($socket);
  }else{
    echo "Online!"; 
    fclose($socket);
  }

});


$app->post('/updateCurrentValue', function($request, $response, $args) {
  $db = new DbHandler();
  $r = json_decode($request->getBody());
  // json基本資訊
  $rmID = $r->rmID;
  $offline = $r->offline;

  // 取得rm資訊
  $rmResult = getRmType($db, $rmID);
  $rmType = $rmResult['rmType'];
  $rmIP = $rmResult['rmIP'];

  // 感應器資訊
  $value = array($r->ch1, $r->ch2, $r->ch3, $r->ch4);
  $sensor = array("ch1", "ch2", "ch3", "ch4");

  // 當月及前月字串
  $date = date("Ym");
  $predate = date("Ym", strtotime(" -1 month"));

  // 若為rm2則多收溫濕度感應器資訊
  if ($rmType=="RM2" || $rmType=="RM") {
    array_push($value, $r->temp, $r->hum);
    array_push($sensor, "temp", "rh");
  }

  for ($i=0; $i<count($sensor); $i++){
    // 取得感應器資訊
    $sensorInfo = getCurrentData($db, $rmID, $sensor[$i]);
    $uid = $sensorInfo['uid'];
    $pause = $sensorInfo['pause'];
    $name = $sensorInfo['name'];

    if($pause!=1){

      // 判斷數值狀態(0無,1正常,2告警,3警示,4離線)
      $status = 0;
      if($offline==0){
        if(between($sensorInfo['warning_min'], $sensorInfo['warning_max'], $value[$i])){
          $status = 1;
        }else if(between($sensorInfo['alert_min'], $sensorInfo['alert_max'], $value[$i])){
          $status = 2;
          setSensorAlert($db, $rmID, $sensor[$i], $status);
        }else{
          $status = 3;
          setSensorAlert($db, $rmID, $sensor[$i], $status);
        }

        $update = $db->getUpdate("UPDATE general_info_rm SET offline='{$offline}', update_date = LOCALTIMESTAMP, update_by = 'admin' WHERE rmID='{$rmID}' AND offline<>'{$offline}'; ");
        // 檢查上個月資料表是否存在, 若不存在則建立新表
        if(!checkHistoryTable($db, $uid, $predate)) {
          createHistoryTable($db, $uid, $predate);
        }

        // 檢查本月資料表是否存在, 若存在則寫入資訊, 若不存在則建立新表後寫入資訊
        if(checkHistoryTable($db, $uid, $date)){
          insertHistoryTable($db, $uid, $rmIP, $name, $date, $value[$i], $status);
        }else{
          createHistoryTable($db, $uid, $date);
          insertHistoryTable($db, $uid, $rmIP, $name, $date, $value[$i], $status);
        }
      }else{
        $status = 4;
        if(checkHistoryTable($db, $uid, $date)){
          insertHistoryTable($db, $uid, $rmIP, $name, $date, $value[$i], $status);
        }else{
          createHistoryTable($db, $uid, $date);
          insertHistoryTable($db, $uid, $rmIP, $name, $date, $value[$i], $status);
        }

        $update = $db->getUpdate("UPDATE general_info_rm SET offline='{$offline}', update_date = LOCALTIMESTAMP, update_by = 'admin' WHERE rmID='{$rmID}' AND offline<>'{$offline}'; ");
        setSensorAlert($db, $rmID, $sensor[$i], $status);
      }
      // 寫入警示通知表
        // 取得前次感應器數值
        $currentData = getCurrentData($db, $rmID, $sensor[$i]);
        // 更新感應器最新數值
        setCurrentData($db, $currentData['lastValue'], $currentData['lastDataTime'], "0","0","0", $value[$i], $currentData['uid'], $status);
    }
  }

});



?>