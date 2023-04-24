<?php
ini_set("memory_limit","2048M");
$app->get('/noticeList', function($request, $response, $args){
  $db = new DbHandler();

  $result = $db->getQuery("SELECT nt.rmid, nt.port, rsc.dname as name, rsc.model, rsc.dtype as isfan, rsc.typename as type, rsc.dlocation as location, 
                                  rsc.isused as `use`, rsc.ispaused as `pause`, nt.dev_status, nt.datatime, nt.noticeuid FROM notice_table nt 
                                LEFT JOIN rm_sensor_combined rsc ON nt.rmID = rsc.rmID AND nt.port=rsc.port 
                                WHERE nt.dev_status > 1 AND rsc.isused = 1 ORDER BY nt.datatime DESC;");

    $return = array();
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $obj = new StdClass();
        $obj->uid = (int)$row["noticeuid"];
        $obj->rmID = $row["rmid"];
        $obj->port = $row["port"];
        $obj->name = $row["name"];
        $obj->model = $row["model"];
        $obj->isfan = (int)$row["isfan"];
        $obj->type = $row["type"];
        $obj->location = $row["location"];
        $obj->use = (int)$row["use"];
        $obj->pause = (int)$row["pause"];
        $obj->status = (int)$row["dev_status"];
        $obj->datatime = $row["datatime"];
        array_push($return, $obj);
      }
    }
    return $response->withJson($return, 200);

});

$app->post('/readNotice', function($request, $response, $args){
  $db = new DbHandler();
  $session = $db->getSession();
  $r = json_decode($request->getBody());
  $uid = $r->uid;

  $update = $db->getUpdate("UPDATE notice_table SET dev_status = 1, datatime=localtimestamp, update_date = LOCALTIMESTAMP, update_by = '{$session['name']}' WHERE noticeuid = '{$uid}' AND dev_status <> 1;");
  
  $result = $db->getQuery("SELECT nt.rmID, nt.port, rsc.dname as name, rsc.model, rsc.dtype as isfan, rsc.typename as type, rsc.dlocation as location, 
                                rsc.isused `use`, rsc.ispaused pause, nt.dev_status, nt.datatime, nt.noticeuid FROM notice_table nt 
                          LEFT JOIN rm_sensor_combined rsc ON nt.rmID = rsc.rmID AND nt.port=rsc.port 
                          WHERE nt.dev_status > 1 AND rsc.isused = 1 ORDER BY nt.datatime DESC;");

    $return = array();
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $obj = new StdClass();
        $obj->uid = (int)$row["noticeuid"];
        $obj->rmID = $row["rmID"];
        $obj->port = $row["port"];
        $obj->name = $row["name"];
        $obj->model = $row["model"];
        $obj->isfan = (int)$row["isfan"];
        $obj->type = $row["type"];
        $obj->location = $row["location"];
        $obj->use = (int)$row["use"];
        $obj->pause = (int)$row["pause"];
        $obj->status = (int)$row["dev_status"];
        $obj->datatime = $row["datatime"];
        array_push($return, $obj);
      }
    }
    return $response->withJson($return, 200);
});

$app->post('/locationList', function($request, $response, $args) {
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
    $result = $db->getQuery("SELECT DISTINCT dlocation FROM rm_sensor_combined WHERE dtype = 0 AND isused = 1");
    $return = array();
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        array_push($return, $row["dlocation"]);
      }
    }
    return $response->withJson($return, 200);
  }
});

$app->post('/modelList', function($request, $response, $args) {
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
    $result = $db->getQuery("SELECT DISTINCT model FROM rm_sensor_combined WHERE dtype = 0 AND isused = 1");
    $return = array();
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        array_push($return, $row["model"]);
      }
    }
    return $response->withJson($return, 200);
  }
});

$app->post('/viewList', function($request, $response, $args) {
  $r = json_decode($request->getBody());
  $return = array();
  $db = new DbHandler();
  $session = $db->getSession();

  $result = $db->getQuery("SELECT DISTINCT rmID, rmIP, off_line as `offline` FROM general_info_rm");
  $ipArray = array();
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $obj = new StdClass();
      $obj->rmIP = $row["rmIP"];
      $obj->status = 0;
      $obj->offline = (int)$row["offline"];
      array_push($ipArray, $obj);
    }
  }
  
  if (!isset($session['uid'])){
    $return["status"] = "錯誤";
    $return["message"] = "未登入狀態";
    return $response->withJson($return, 201);
  }else{
    $auth = $session["auth"];

    $result = $db->getQuery("SELECT rsc.rscuid, rsc.rmid, gir.usbrelayid, rsc.rmip, rsc.port, rsc.dname, rsc.model, rsc.dtype, rsc.typename, rsc.unit, rsc.warning_max, rsc.warning_min, rsc.alert_max, rsc.alert_min, rsc.photo, rsc.location1, rsc.location2
                                  , rsc.location3, rsc.dev_status `status`, rsc.dlocation, rsc.isused, rsc.ispaused, rsc.last_data_time lastdatatime, rsc.last_value lastvalue, rsc.previous_data_time previousdatatime, rsc.previous_value previousvalue
                              FROM rm_sensor_combined rsc left join general_info_rm gir on gir.giruid = rsc.giruid
                              WHERE rsc.dtype = 0 AND rsc.isused = 1 ");

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
        $obj->use = (int)$row["isused"];
        $obj->status = (int)$row["status"];
        $obj->pause = (int)$row["ispaused"];
        $obj->lastDataTime = $row["lastdatatime"];
        $obj->value = (float)$row["lastvalue"];
        $obj->previousDataTime = $row["previousdatatime"];
        $obj->previousValue = (float)$row["previousvalue"];

        for ($i=0; $i < count($ipArray); $i++) { 
          if($ipArray[$i]->rmIP==$obj->rmIP){
            if($ipArray[$i]->offline==1){
              $obj->status = 4;
            }else if($obj->pause==1){
              $obj->status = 5;
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
    // pause 暫停儲存數據 (0=正常, 1=暫停)
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

$app->post('/viewSensorContent', function($request, $response, $args) {
  $r = json_decode($request->getBody());
  $return = array();
  $db = new DbHandler();
  $session = $db->getSession();
  $uid = $r->uid;
  if (!isset($session['uid'])){
    $return["status"] = "錯誤";
    $return["message"] = "未登入狀態";
    return $response->withJson($return, 201);
  }else{
    $auth = $session["auth"];

    $result = $db->getQuery("SELECT rsc.rscuid, rsc.rmid, rsc.rmip, rsc.port, rsc.dname, rsc.model, rsc.dtype, rsc.typename, rsc.unit, rsc.warning_max, rsc.warning_min, rsc.alert_max, rsc.alert_min, rsc.photo, rsc.location1, rsc.location2
                                  , rsc.location3, rsc.dlocation, rsc.isused, rsc.ispaused, rsc.last_data_time lastdatatime, rsc.last_value lastvalue, rsc.previous_data_time previousdatatime, rsc.previous_value previousvalue
                              FROM rm_sensor_combined rsc WHERE rsc.rscuid = '$uid' ;");

    $obj = new StdClass();
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {

        $obj->uid = (int)$row["rscuid"];
        $obj->rmID = $row["rmid"];
        $obj->rmIP = $row["rmip"];
        $obj->port = $row["port"];
        $obj->name = $row["dname"];
        $obj->model = $row["model"];
        $obj->isfan = (int)$row["dtype"];
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
        $obj->use = (int)$row["isused"];
        $obj->pause = (int)$row["ispaused"];
        $obj->lastDataTime = $row["lastdatatime"];
        $obj->value = (float)$row["lastvalue"];
        $obj->previousDataTime = $row["previousdatatime"];
        $obj->previousValue = (float)$row["previousvalue"];

        if(between($obj->warning_min, $obj->warning_max, $obj->value)){
          $obj->status = 1;
        }else if(between($obj->alert_min, $obj->alert_max, $obj->value)){
          $obj->status = 2;
        }else{
          $obj->status = 3;
        }
      }
    }
    //return $response->withJson($obj, 200);
    
    return $response->withJson($obj, 200);
  }
});

$app->post('/sensorToday', function($request, $response, $args) {
  $r = json_decode($request->getBody());
  $return = array();
  $db = new DbHandler();
  $date = date("Ym");
  $session = $db->getSession();
  $uid = $r->uid;
  $today = date("Y-m-d");
  $tomorrow = date("Y-m-d", strtotime(" 1 day"));

  if (!isset($session['uid'])){
    $return["status"] = "錯誤";
    $return["message"] = "未登入狀態";
    return $response->withJson($return, 201);
  }else{
    $auth = $session["auth"];


    $result = $db->getQuery("SELECT * FROM current_history_data_{$uid}_{$date} WHERE create_date>='$today' AND create_date<'$tomorrow'");
    $return = new StdClass();
    $return->time = array();
    $return->value = array();
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        array_push($return->time, $row["create_date"]);
        array_push($return->value, (float)$row["value"]);
      }
    }
    return $response->withJson($return, 200);
    
  }
});

$app->post('/sensorRange', function($request, $response, $args) {
  $r = json_decode($request->getBody());
  $return = array();
  $db = new DbHandler();
  $date = date("Ym");
  $predate = date("Ym", strtotime(" -1 month"));
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

    $result = $db->getQuery("SELECT * FROM current_history_data_{$uid}_{$predate} WHERE create_date>='$startDate' AND create_date<'$endDate' UNION SELECT * FROM current_history_data_{$uid}_{$date} WHERE create_date>='$startDate' AND create_date<'$endDate' ORDER BY create_date");
    
	$return = new StdClass();
    $return->time = array();
    $return->value = array();
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        array_push($return->time, $row["create_date"]);
        array_push($return->value, (float)$row["value"]);
      }
    }
    return $response->withJson($return, 200);
  }
});

$app->post('/sensorAlert', function($request, $response, $args) {
  $r = json_decode($request->getBody());
  $return = array();
  $db = new DbHandler();
  $date = date("Ym");
  $predate = date("Ym", strtotime(" -1 month"));
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

    $result = $db->getQuery("
                SELECT EXTRACT(YEAR FROM create_date) as year, EXTRACT(MONTH FROM create_date) as month, EXTRACT(DAY FROM create_date) as day,
                       COUNT(CASE WHEN status = 2 THEN status END) warning,
                       COUNT(CASE WHEN status = 3 THEN status END) alert
                  FROM current_history_data_{$uid}_{$date} WHERE create_date>='$startDate' AND create_date<'$endDate'
                  GROUP BY year, month, day 
                UNION SELECT EXTRACT(YEAR FROM create_date) as year, EXTRACT(MONTH FROM create_date) as month, EXTRACT(DAY FROM create_date) as day,
                              COUNT(CASE WHEN status = 2 THEN status END) warning,
                              COUNT(CASE WHEN status = 3 THEN status END) alert
                  FROM current_history_data_{$uid}_{$predate} WHERE create_date>='$startDate' AND create_date<'$endDate'
                  GROUP BY year, month, day ORDER BY year, month, day; ");

    $return = new StdClass();
    $return->time = array();
    $return->warning = array();
    $return->alert = array();

    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $monthStr = ($row["month"]<10)?'0'.$row["month"]:$row["month"];
        $dayStr = ($row["day"]<10)?'0'.$row["day"]:$row["day"];
        $dateStr = $row["year"].'-'.$monthStr.'-'.$dayStr;
        array_push($return->time, $dateStr);
        array_push($return->warning, $row["warning"]);
        array_push($return->alert, $row["alert"]);
      }
    }
    return $response->withJson($return, 200);
  }
});

$app->post('/downloadRangeCSV', function($request, $response, $args) {
  $r = json_decode($request->getBody());
  $uid = $r->uid;
  $rmID = $r->rmID;
  $port = $r->port;
  $startDate = $r->startDate;
  $endDate = date("Y-m-d", strtotime(" 1 day", strtotime($r->endDate)));

  $db = new DbHandler();
  $date = date("Ym");
  $predate = date("Ym", strtotime(" -1 month"));

  $csv_filename = $rmID.'('.$port.')';
  $csv_export = '';

  $result = $db->getQuery("SELECT rmIP, sensor , create_date , value, status  FROM current_history_data_{$uid}_{$predate} WHERE create_date>='$startDate' AND create_date<'$endDate' 
                            UNION SELECT rmIP, sensor , create_date , value, status  FROM current_history_data_{$uid}_{$date} WHERE create_date>='$startDate' AND create_date<'$endDate' ORDER BY create_date");


$csv_export = '"Device IP", "Sensor", "Data Time", "Data Value", "Status"';
$csv_export.= '';

  if ($result->num_rows > 0) {
    $csv_export.= chr(13);
    while ($row = $result->fetch_assoc()) {
      $csv_export.= '"'.$row['rmIP'].'",';
      $csv_export.= '"'.$row['sensor'].'",';
      $csv_export.= '"'.$row['create_date'].'",';
      $csv_export.= '"'.$row['value'].'",';
      if($row['status']==1){
        $csv_export.= '"normal",';
      }else if($row['status']==2){
        $csv_export.= '"warning",';
      }else if($row['status']==3){
        $csv_export.= '"alert",';
      }else if($row['status']==4){
        $csv_export.= '"Offline",';
      }else{
        $csv_export.= '"'.$row['status'].'",';
      }
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

$app->post('/downloadAlarmRangeCSV', function($request, $response, $args) {
  $r = json_decode($request->getBody());
  $uid = $r->uid;
  $rmID = $r->rmID;
  $port = $r->port;
  $startDate = $r->startDate;
  $endDate = date("Y-m-d", strtotime(" 1 day", strtotime($r->endDate)));

  $db = new DbHandler();
  $date = date("Ym");
  $predate = date("Ym", strtotime(" -1 month"));

  $csv_filename = $rmID.'('.$port.')';
  $csv_export = '';

  $result = $db->getQuery("SELECT rmIP, sensor , create_date , value, status  FROM current_history_data_{$uid}_{$predate} WHERE  Status > 1 AND  create_date>='$startDate' AND create_date<'$endDate' 
                            UNION SELECT rmIP, sensor , create_date , value, status  FROM current_history_data_{$uid}_{$date} WHERE  Status > 1 AND create_date>='$startDate' AND create_date<'$endDate' ORDER BY create_date");

  $csv_export = 'Device IP, Sensor, Data Time, Data Value, Status';
  $csv_export.= '';

  if ($result->num_rows > 0) {
    $csv_export.= chr(13);
    while ($row = $result->fetch_assoc()) {
      $csv_export.= '"'.$row['rmIP'].'",';
      $csv_export.= '"'.$row['sensor'].'",';
      $csv_export.= '"'.$row['create_date'].'",';
      $csv_export.= '"'.$row['value'].'",';
      if($row['status']==1){
        $csv_export.= '"normal",';
      }else if($row['status']==2){
        $csv_export.= '"warning",';
      }else if($row['status']==3){
        $csv_export.= '"alert",';
      }else if($row['status']==4){
        $csv_export.= '"Offline",';
      }else{
        $csv_export.= '"'.$row['status'].'",';
      }
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

function between($min, $max, $value){
  //處理成陣列
  if (is_array($value)){
    $limit = $value;
  }else{
    $limit = explode(",", $value);
  }
  //合併成多個數值
  $value = array_merge($limit, $limit);

  $limit[] = $max;
  $limit[] = $min;

  //使用max及min函數判斷是否在區間內
  if ((max($limit) == $max && min($limit) == $min) || (max($value) == $max && min($value) == $min)){
    $result = true;
  }else{
    $result = false;
  }

  return $result;
}

?>