<?php

function checksum16($data) {
    $checksum = 0;
    for ($i = 0; $i < 24; $i++) {
        $hex = implode(unpack("H*", $data[$i]));
        $c = $checksum;
        $checksum ^= hexdec($hex);
    }
    if($checksum>15)
    return pack("H*", dechex($checksum));
    else
    return pack("h*", dechex($checksum));
}  

function sendDataToRMG($alarmUpdate, $cmd, $channel, $rmgid, $channelData){
    $data = array_fill(0, 25, 'U');
    $data[0] = 'F';
    $data[1] = 'A';
    $data[2] = $cmd;  
    $data[3] = $channel;
    if($rmgid>15){
        $data[4] =  pack("H*", dechex($rmgid)); //CHx RS485 ID1 如果ID >0X10 要改成=>  pack("H*", 大寫H  
    }else{
        $data[4] =  pack("h*", dechex($rmgid)); //CHx RS485 ID1 如果ID >0X10 要改成=>  pack("H*", 大寫H  
    }        
    if($alarmUpdate){
        for($i=0;$i<count($channelData);$i++){
            if($channelData[$i]>15){
                $data[$i+5] = pack("H*", dechex($channelData[$i]));
            }else{
                $data[$i+5] = pack("h*", dechex($channelData[$i]));
            }
        }   
    }
    //echo json_encode($data);
    $data[24] =checksum16(implode('', $data));
    $message = implode('', $data);

    return bin2hex($message);
}

$app->post('/rmgViewList', function($request, $response, $args) {
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

        $result = $db->getQuery(" SELECT rsc.rscuid, rsc.rmid, rsc.rmip, rsc.port, rsc.dname, rsc.model, rsc.dtype, rsc.iszigbee isZigbee, rsc.typename, rsc.unit, rsc.alert_max, 
                                            rsc.location1, rsc.location2, rsc.location3, rsc.dlocation, rsc.isused, rsc.ispaused, rsc.last_data_time lastDataTime, rsc.last_value lastvalue, 
                                            rsc.previous_data_time previousDataTime, rsc.previous_value previousValue, rsc.zigbee_signal zigbeeSignal, rsc.dev_status, rxt.buzzerState
                                    FROM rm_sensor_combined rsc LEFT JOIN rmg_Except_Table rxt on rxt.rscuid = rsc.rscuid WHERE rsc.dtype =-1 AND rsc.isused = 1 order by rsc.isused ;");
        
        $return = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {

                $obj = new StdClass();
                $obj->uid = (int)$row["rscuid"];
                $obj->rmip = $row["rmip"];
                $obj->rmID = $row["rmid"];
                $obj->buzzerState = (int)$row["buzzerState"];
                $obj->port = $row["port"];
                $obj->name = $row["dname"];
                $obj->isfan = (int)$row["dtype"];
                $obj->unit = $row["unit"];
                $obj->alert_max = (float)$row["alert_max"];
                $obj->location = $row["dlocation"];
                $obj->pause = (int)$row["ispaused"];
                $obj->isZigbee = (int)$row["isZigbee"];
                $obj->zigbeeSignal = (int)$row["zigbeeSignal"];
                $obj->value = (float)$row["lastvalue"];
                $obj->status = (float)$row["dev_status"];

                if((int)$row["zigbeeSignal"] <= 20) {
                    $obj->zigbeeSignalLayer = 1;
                } else if((int)$row["zigbeeSignal"] > 20 && (int)$row["zigbeeSignal"] <= 40) {
                    $obj->zigbeeSignalLayer = 2;
                } else if((int)$row["zigbeeSignal"] > 40 && (int)$row["zigbeeSignal"] <= 60) {
                    $obj->zigbeeSignalLayer = 3;
                } else if((int)$row["zigbeeSignal"] > 60 && (int)$row["zigbeeSignal"] <= 80) {
                    $obj->zigbeeSignalLayer = 4;
                } else if((int)$row["zigbeeSignal"] > 80) {
                    $obj->zigbeeSignalLayer = 5;
                }

                array_push($return, $obj);
            }
        }
        return $response->withJson($return, 200);
    }
});

$app->post('/rmglocationList', function($request, $response, $args) {
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
        $result = $db->getQuery("SELECT DISTINCT dlocation FROM rm_sensor_combined WHERE dtype =-1 AND isused = 1 ;");
        $return = array();
        if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            array_push($return, $row["dlocation"]);
        }
        }
        return $response->withJson($return, 200);
    }
});

$app->post('/closeRMGAlarm', function($request, $response, $args) {

    $db = new DbHandler();
    $session = $db->getSession();
    
    $r = json_decode($request->getBody());       
    $uid = $r->uid;
    $portLike = $r->channel."-".$r->rmgid."-".'rmg%';

    if ($r->buzzerState == 0) {
        $update = $db->getUpdate("UPDATE rmg_Except_Table SET buzzerState = 1, update_date = LOCALTIMESTAMP, update_by = '{$session['name']}' WHERE rscuid= $uid AND buzzerState <> 1 ; ");
    }else {
        $update = $db->getUpdate("UPDATE rmg_Except_Table SET buzzerState = 0, update_date = LOCALTIMESTAMP, update_by = '{$session['name']}' WHERE rscuid= $uid AND buzzerState <> 0 ; ");
    }
    $result = $db->getQuery(" SELECT buzzerState FROM rmg_Except_Table  WHERE rmgid =  $r->rmgid ;");
    
    $chData = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            array_push($chData, (int)$row["buzzerState"]);
        }
        $actionCode = sendDataToRMG(true, 'E', $r->channel, (int)$r->rmgid, $chData);
        setRMAction($r->rmip, $uid, $r->rmID, $actionCode, "/RMI-CH/set_RMG_SingleAlarm.php", $note = '開關閉警報功能');
        
        return $actionCode;
    }

    $channelData = [];

    return ""; //file_get_contents($url);
});

$app->post('/getRMGContent', function($request, $response, $args){
    $r = json_decode($request->getBody());
    $db = new DbHandler();
    $subIDtmp = "";
    $rmIDtmp = "";
    
    $result = $db->getQuery("SELECT rsc.giruid as rmuid, rsc.rmID, rsc.rmip, rsc.subID, rsc.isZigbee, rsc.port, ismuted FROM rm_sensor_combined rsc left join rmg_Except_Table on rsc.rscuid = rmg_Except_Table.rscuid  WHERE rsc.rscuid = $r->uid ; ");
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $return["rmuid"] = $row["rmuid"];
            $return["rmID"] = $rmIDtmp = $row["rmID"];
            $return["rmip"] = $row["rmip"];
            $return["port"] = $row["port"];
            $return["mute"] = (int)$row["ismuted"];
            $return["isZigbee"] = (int)$row["isZigbee"];
            $return["rmglist"] = array();

            $subIDtmp = $row["subID"];
        }

        $result = $db->getQuery(" SELECT rscuid, dname `name`, alert_max, dlocation `location`, isZigbee, isPaused FROM rm_sensor_combined where rmID = '$rmIDtmp' and subID = $subIDtmp order by rscuid; ");
        
        if($result->num_rows > 0){
            while ($row = $result->fetch_assoc()) {
                $obj = new StdClass();
                $obj->uid = (int)$row["rscuid"];
                $obj->name = $row["name"];
                $obj->alert_max = (float)$row["alert_max"];
                $obj->pause = (int)$row["isPaused"];
                $obj->isZigbee = (int)$row["isZigbee"];
                $obj->location = $row["location"];

                array_push($return["rmglist"], $obj);
            }
        }
    }
    
    return $response->withJson($return, 201);
});

$app->post('/modifyRMGContent', function($request, $response, $args){
    $r = json_decode($request->getBody());
    $db = new DbHandler();
    $session = $db->getSession();
    $ismuted = $r->mute;
    $cmd = '';
    $alarmUpdate = false;
    $alrmUpdate  = false;
    $rmguid = $r->channeldata[0]->rmguid;
    $buzzerState = 0;

    $result = $db->getQuery("SELECT rsc.rmip, rsc.rmid, rsc.port, rsc.subid, rsc.alert_max, buzzerState 
                                FROM rm_sensor_combined rsc left join rmg_Except_Table 
                                on rsc.rscuid = rmg_Except_Table.rscuid WHERE rsc.rscuid = $rmguid ; ");     

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $r->rmip = $row["rmip"];
            $r->rmid = $row["rmid"];
            $port = $row["port"];
            $buzzerState = $row["buzzerState"];
            $port = explode("-", $port);
            $r->channel = $port[0];
            $r->rmgid = $port[1];
            $ch = $r->channel;                
            $subIDtmp = $row["subid"];
        }
    }

    //更新alarm check狀態
    $channelData = [];
    $sresult = $db->getQuery("SELECT ismuted FROM rmg_Except_Table 
                                WHERE rmgID = $subIDtmp AND ismuted <> $ismuted ; ");       
    
    if ($sresult->num_rows > 0) {
        while ($row = $sresult->fetch_assoc()) {
            $mute = (int)$row["ismuted"];

            if($ismuted == "0"){
                $cmd = 'O';
            }else if($ismuted == "1"){
                $cmd = 'B';                    
            }
            $rmUpdate = $db->getUpdate("UPDATE rmg_Except_Table SET ismuted = $ismuted, update_date = LOCALTIMESTAMP, update_by = '{$session['name']}' WHERE rmgID = $subIDtmp AND ismuted <> $ismuted; ");               
            
        } 
        $actionCode = sendDataToRMG($alarmUpdate, $cmd, $r->channel, (int)$r->rmgid, $channelData);
        setRMAction($r->rmip, $rmguid, $r->rmid, $actionCode, "/RMI-CH/RMG_Alarm_Off.php", $note = '勾選以關閉蜂鳴器');           
        $cmd = '';
    }

    //更新chanel的alarm及開關
    $aresult = $db->getQuery("SELECT alert_max, rscuid, ispaused FROM rm_sensor_combined WHERE subid = $subIDtmp  ; ");        
    if ($aresult->num_rows > 0) {
        while ($row = $aresult->fetch_assoc()) {
            for($i=0;$i<count($r->channeldata);$i++){
                $alrm = $r->channeldata[$i]->alarm;
                $pause = $r->channeldata[$i]->pause;
                $rscuid = $r->channeldata[$i]->rmguid;
                if (($row["alert_max"] != $alrm || $row["ispaused"] != $pause) && $rscuid == $row["rscuid"]){
                    $alrmUpdate = true;
                }
            }
        }
    }    
    
    $alarmUpdate = $alrmUpdate;
    for($i=0;$i<count($r->channeldata);$i++){
        $rmguid = $r->channeldata[$i]->rmguid;
        $alarm = $r->channeldata[$i]->alarm;
        $pause = $r->channeldata[$i]->pause;
        $name = $r->channeldata[$i]->name;
        $location = $r->channeldata[$i]->location;

        $rmUpdate = $db->getUpdate("UPDATE rm_sensor_combined 
                                    SET alert_max='$alarm', isPaused = '$pause', dname = '{$name}',  dlocation = '{$location}', 
                                        update_date = LOCALTIMESTAMP,  update_by = '{$session['name']}'  
                                    WHERE rscuid='$rmguid' AND ( alert_max <> '$alarm' OR isPaused <> '$pause'
                                            OR dname <> '{$name}' OR COALESCE(dlocation,'')  <> '{$location}'); ");
        

        if($r->channeldata[$i]->pause == 1){
            array_push($channelData,0);  
        }else{
            array_push($channelData,$r->channeldata[$i]->alarm * 10);
        }
    }
    if ($alrmUpdate){
        $cmd = 'Z';
        $actionCode = sendDataToRMG($alarmUpdate, $cmd, $r->channel,(int)$r->rmgid,$channelData);
        setRMAction($r->rmip, $rmguid, $r->rmid, $actionCode, "/RMI-CH/set_RMG_Posts.php", $note = 'RMG報警值設置');           
                    
        $alrmUpdate = false;
        $alarmUpdate = false;
    }
    
    return $response->withJson($r, 201);
});

$app->post('/getRmgReport', function($request, $response, $args){
    $r = json_decode($request->getBody());
    $db = new DbHandler();
    $TrmID = str_replace('-','_', $r->rmID);
    $tablename = "`current_history_rmg_".$TrmID."_".$r->rmguid."`";
    $startDate = $r->startDate;
    $endDate = date("Y-m-d", strtotime(" 1 day", strtotime($r->endDate)));
    
    $result = $db->getQuery("SELECT chg.rmIP, chg.rmgid, chg.data1, chg.data2, chg.data3, chg.data4, chg.data5, chg.data6, chg.data7, chg.data8, chg.create_date 
                                FROM ".$tablename." as chg WHERE chg.create_date >= '$startDate' AND chg.create_date < '$endDate'; ");

    $csv_filename = $tablename;
    $csv_export = 'IP Address, RMG ID, Data Time, Data1, Data2, Data3, Data4, Data5, Data6, Data7, Data8';
    $csv_export.= '';

    if ($result->num_rows > 0) {
        $csv_export.= chr(13);
        while ($row = $result->fetch_assoc()) {

            $csv_export.= '"'.$row['rmip'].'",';
            $csv_export.= '"'.$row['rmgid'].'",';
            $csv_export.= '"'.$row['create_date'].'",';
            $csv_export.= '"'.$row['data1'].'",';
            $csv_export.= '"'.$row['data2'].'",';
            $csv_export.= '"'.$row['data3'].'",';
            $csv_export.= '"'.$row['data4'].'",';
            $csv_export.= '"'.$row['data5'].'",';
            $csv_export.= '"'.$row['data6'].'",';
            $csv_export.= '"'.$row['data7'].'",';
            $csv_export.= '"'.$row['data8'].'",';

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

$app->post('/getRmgAlarmReport', function($request, $response, $args){
    $r = json_decode($request->getBody());
    $db = new DbHandler();
    $tablename = "alarm_history_rmg";
    $startDate = $r->startDate;
    $endDate = date("Y-m-d", strtotime(" 1 day", strtotime($r->endDate)));
    
    $result = $db->getQuery("SELECT rmip, post, rmgid, alarm, create_date FROM ".$tablename." WHERE rmip = (select rmip from general_info_rm where giruid = ".$r->rmuid." ) AND rmgid = ".$r->rmguid." AND create_date >= '$startDate' AND create_date < '$endDate'; ");
    
    $csv_filename = $tablename;
    $csv_export = 'IP Address,RMG ID, G Station, Data Time,Alarm';
    $csv_export.= '';
    if ($result->num_rows > 0) {
        $csv_export.= chr(13);
        while ($row = $result->fetch_assoc()) {
            $csv_export.= '"'.$row['rmip'].'",';
            $csv_export.= '"'.$row['rmgid'].'",';
            $csv_export.= '"'.$row['post'].'",';
            $csv_export.= '"'.$row['create_date'].'",';
            $csv_export.= '"'.$row['alarm'].'"';
            
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
?>