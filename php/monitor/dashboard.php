<?php

$app->post('/realtimeList', function($request, $response, $args) {
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
    $result = $db->getQuery("SELECT 
                              (SELECT count(dev_status) FROM rm_sensor_combined where dtype = 0 and isused = 1 and dev_status in (2, 3)) rmAlarm, 
                              (SELECT count(dev_status) FROM rm_sensor_combined where dtype = 0 and isused = 1 and dev_status in (0, 4)) rmOff, 
                              (SELECT count(dev_status) FROM rm_sensor_combined where dtype = 0 and isused = 1 and dev_status = 1) rmDefault,
                              (SELECT count(dev_status) FROM rm_sensor_combined where dtype in (1,2,3)  and isused = 1 and dev_status = 3) rmiAlarm, 
                              (SELECT count(dev_status) FROM rm_sensor_combined where dtype in (1,2,3)  and isused = 1 and dev_status in (0,4)) rmiOff, 
                              (SELECT count(dev_status) FROM rm_sensor_combined where dtype in (1,2,3)  and isused = 1 and dev_status = 1) rmiDefault,
                              (SELECT count(dev_status) FROM rm_sensor_combined where dtype = -1  and isused = 1 and dev_status = 3) rmgAlarm, 
                              (SELECT count(dev_status) FROM rm_sensor_combined where dtype = -1  and isused = 1 and dev_status in (0,4)) rmgOff, 
                              (SELECT count(dev_status) FROM rm_sensor_combined where dtype = -1  and isused = 1 and dev_status = 1) rmgDefault
                              , DATE_FORMAT(now(),'%I:%i:%s %p') nowTime, DATE_FORMAT(now(),'%W, %M %d, %Y') as nowDate ; ");
            
    $return = array();
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {

        $obj = new StdClass();        
        $obj->rmDefault = (int)$row["rmDefault"];
        $obj->rmOff = (int)$row["rmOff"];
        $obj->rmAlarm = (int)$row["rmAlarm"];
        $obj->rmiDefault = (int)$row["rmiDefault"];
        $obj->rmiOff = (int)$row["rmiOff"];
        $obj->rmiAlarm = (int)$row["rmiAlarm"];
        $obj->rmgDefault = (int)$row["rmgDefault"];
        $obj->rmgOff = (int)$row["rmgOff"];
        $obj->rmgAlarm = (int)$row["rmgAlarm"];
        $obj->nowTime = $row["nowTime"];
        $obj->nowDate = $row["nowDate"];

        array_push($return, $obj);
      }
    }
    return $response->withJson($return, 200);
  }
});

$app->post('/weeklyList', function($request, $response, $args) {
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
    $result = $db->getQuery(" select '1' rmAlarm, '14' rmOff, '8' rmDefault, '0' rmiAlarm, '4' rmiOff, '17' rmiDefault, '1' rmgAlarm, '0' rmgOff, '16' rmgDefault , DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 0 DAY),'%y-%m-%d')  as DataDate    
                              union select '1' rmAlarm, '10' rmOff, '8' rmDefault, '0' rmiAlarm, '0' rmiOff, '10' rmiDefault, '3' rmgAlarm, '0' rmgOff, '16' rmgDefault , DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY),'%y-%m-%d') as DataDate  
                              union select '1' rmAlarm, '5' rmOff, '8' rmDefault, '1' rmiAlarm, '0' rmiOff, '18' rmiDefault, '2' rmgAlarm, '2' rmgOff, '14' rmgDefault , DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 2 DAY),'%y-%m-%d') as DataDate  
                              union select '0' rmAlarm, '0' rmOff, '15' rmDefault, '1' rmiAlarm, '0' rmiOff, '18' rmiDefault, '0' rmgAlarm, '1' rmgOff, '15' rmgDefault , DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 3 DAY),'%y-%m-%d') as DataDate  
                              union select '3' rmAlarm, '0' rmOff, '12' rmDefault, '1' rmiAlarm, '1' rmiOff, '18' rmiDefault, '1' rmgAlarm, '1' rmgOff, '15' rmgDefault , DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 4 DAY),'%y-%m-%d') as DataDate  
                              union select '0' rmAlarm, '6' rmOff, '3' rmDefault, '0' rmiAlarm, '3' rmiOff, '18' rmiDefault, '0' rmgAlarm, '0' rmgOff, '16' rmgDefault , DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 5 DAY),'%y-%m-%d') as DataDate  
                              union select '2' rmAlarm, '0' rmOff, '1' rmDefault, '3' rmiAlarm, '2' rmiOff, '18' rmiDefault, '0' rmgAlarm, '0' rmgOff, '16' rmgDefault , DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 6 DAY),'%y-%m-%d') as DataDate  
                              ");
                              
    $return = array();
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        
        $obj = new StdClass();        
        $obj->rmDefault = (int)$row["rmDefault"];
        $obj->rmOff = (int)$row["rmOff"];
        $obj->rmAlarm = (int)$row["rmAlarm"];
        $obj->rmiDefault = (int)$row["rmiDefault"];
        $obj->rmiOff = (int)$row["rmiOff"];
        $obj->rmiAlarm = (int)$row["rmiAlarm"];
        $obj->rmgDefault = (int)$row["rmgDefault"];
        $obj->rmgOff = (int)$row["rmgOff"];
        $obj->rmgAlarm = (int)$row["rmgAlarm"];

        array_push($return, $obj);
      }
    }
    return $response->withJson($return, 200);
  }
});

$app->post('/monthlyList', function($request, $response, $args) {
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
    
    $result = $db->getQuery("select '15' Alarm, '3' Offline, '82' Normal");
    $return = array();
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        
        $obj = new StdClass();        
        $obj->Normal = (int)$row["Normal"];
        $obj->Offline = (int)$row["Offline"];
        $obj->Alarm = (int)$row["Alarm"];

        array_push($return, $obj);
      }
    }
    return $response->withJson($return, 200);
  }
});

$app->post('/alertList', function($request, $response, $args){
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
    
    $result = $db->getQuery("SELECT rsc.rmID, nt.port, dname `name`, model, rsc.dtype `isfan`, typename `type`, dlocation `location`, isused `use`, ispaused `pause` , nt.dev_status `status`, rscuid uid, datatime, 
                                (case when nt.dev_status = 4 and ispaused = 0 then 'Offline' when nt.dev_status in (3,5) and ispaused = 0 then 'Alarm' when nt.dev_status in (2) and ispaused = 0 then 'Warning' when  ispaused = 1 then 'Paused' end) AlarmStatus
                              FROM rm_sensor_combined rsc LEFT JOIN notice_table nt ON rsc.rmid = nt.rmid AND rsc.port = nt.port WHERE isused = 1 AND nt.dev_status>1 ORDER BY datatime DESC ;");
                   
    $return = array();
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $obj = new StdClass();
        $obj->uid = (int)$row["uid"];
        $obj->rmID = $row["rmID"];
        $obj->port = $row["port"];
        $obj->name = $row["name"];
        $obj->model = $row["model"];
        $obj->isfan = (int)$row["isfan"];
        $obj->type = $row["type"];
        $obj->location = $row["location"];
        $obj->use = (int)$row["use"];
        $obj->pause = (int)$row["pause"];
        $obj->status = (int)$row["status"];
        $obj->AlarmStatus = $row["AlarmStatus"];
        $obj->datatime = $row["datatime"];
        array_push($return, $obj);
      }
    }
    return $response->withJson($return, 200);
  }

});

?>