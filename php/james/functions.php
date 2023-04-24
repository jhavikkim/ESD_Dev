<?php
  function playsound(){
    shell_exec("taskkill /f /im playwav.exe");
    $filepath = dirname(__DIR__,2)."\\others\\sound\\playwav.exe";

    pclose(popen("start /B ". $filepath, "r")); 

    // return $pid;
  }

  function ledRun($port, $isopen){

    sleep(1);

    $filepath = dirname(__DIR__,2)."\\others\\led\\go-usb-relay.exe ";
    $shell = "";

    if($isopen){
      $do = "open";
      $shell .= $filepath." -as R0002 -n ".$port." > NUL";
    }else{
      $do = "close";
      $shell .= $filepath." -as R0002 > NUL";
    }

    echo $shell;
    return shell_exec($shell);
  }

  function deleteLastOne(){
    $db = new DbHandler();
    $like = '%current_history_data%';
    
    $tableT = $db->getOneRecord("SELECT table_name, (xpath('/row/cnt/text()', xml_count))[1]::text::int as table_rows
                                        FROM (select table_name, table_schema, query_to_xml(format('select count(*) as cnt from %I.%I', table_schema, table_name), false, true, '') as xml_count
                                              from information_schema.tables where table_schema = 'public' and table_name like '{$like}' 
                                        ) t WHERE (xpath('/row/cnt/text()', xml_count))[1]::text::int != 0 ORDER BY SUBSTRING(table_name, LENGTH(table_name)-6, LENGTH(table_name)), table_rows DESC  ");
    
    $tableName = $tableT['table_name'];
    $oldUidT = $db->getOneRecord("; SELECT historyuid FROM $tableName ORDER BY datatime ASC");
    $uid = $oldUidT['historyuid'];
    $deleteOldRow = $db->getUpdate("DELETE FROM $tableName WHERE historyuid = '{$uid}';");
  }

  function deleteLastOneFull(){
    $db = new DbHandler();
    $tableUnionStr ="";
    $like = 'current_history_data%';
    
    $tableDateT = $db->getOneRecord("SELECT table_name FROM (select table_name, table_schema, query_to_xml(format('select count(*) as cnt from %I.%I', table_schema, table_name), false, true, '') as xml_count
                                                                from information_schema.tables where table_schema = 'public' and table_name LIKE '%s_s%' 
                                        )t WHERE (xpath('/row/cnt/text()', xml_count))[1]::text::int != 0 ORDER BY SUBSTRING(table_name, LENGTH(table_name)-6, LENGTH(table_name)) DESC");

    $tableDate = substr($tableDateT["table_name"],strlen($tableDateT["table_name"])-6,strlen($tableDateT["table_name"]));
    $like = $like.$tableDate;
    
    $tablesRes = $db->getQuery("SELECT table_name FROM information_schema.tables WHERE table_name LIKE '{$like}';");
    if($tablesRes->num_rows > 0){
      while ($row = $tablesRes->fetch_assoc()) {
        $tablename = $row['table_name'];
        $tableUnionStr.="SELECT '{$tablename}' as tb_name, historyuid, datatime FROM ".$tablename." UNION ";
      }
      
      $tableUnionStr = substr($tableUnionStr, 0, strlen($tableUnionStr)-6)." ORDER BY datatime ASC";

      $oldUidT = $db->getOneRecord($tableUnionStr);
      if($oldUidT){
        $uid = $oldUidT['historyuid'];
        $lastTable = $oldUidT['tb_name'];
        $deleteOldRow = $db->getUpdate("DELETE FROM $lastTable WHERE historyuid = '{$uid}';");
      }
    }
  }

  function checkTable($db, $tableName){
    $result = $db->getQuery("SHOW TABLES LIKE '{$tableName}'");    
    $exist = $result->num_rows>0;   
    return $exist;
  }

  function setRMAction($rmIP, $rscuid, $rmID, $actionCode, $phpfile, $note = '', $data = array(), $method = 'GET'){
    $db = new DbHandler();
    if(!checkTable($db,'device_action')) {
      createActionTable($db);
    }
    $url_path = "http://localhost/esd_dev/actionfiles/".$phpfile;
   // echo $url_path ;
    $options = array( 
      'http' => array( 
      'method' => $method, 
      'content' => http_build_query($data)) 
    ); 
      
    // Create a context stream with the specified options 
    $stream = stream_context_create($options); 
    
    if ($actionCode != ""){ // Actions on RMI and RMG

      $result = $actionCode;
    }else { // Actions on RM1 and RM2

      // The data is stored in the result variable 
      $result = file_get_contents($url_path, false, $stream); 
    }

    $db->getInsert("INSERT INTO device_action(rmIP, rscuid, rmID, file_path, action_code, note)
                                VALUES ('$rmIP', $rscuid, '$rmID', '$phpfile', '$result', '$note')");                            

    return $result;
  }

  function getRMAction($rmID){
    $db = new DbHandler();
    if(!checkTable($db,'device_action')) {
      createActionTable($db);
    }

    $t = $db->getOneRecord("; SELECT devuid, action_code FROM device_action WHERE rmID = '{$rmID}' AND dev_status = 0 ");
    if($t && $t["devuid"]){
      $uid = $t["devuid"];
      $db->getUpdate("UPDATE device_action SET dev_status = 1 WHERE devuid='{$uid}'; ");
      return $t["action_code"];
    }

    return "";
  }

  function getSysSet($key) {
    $db = new DbHandler();
    if(!checkTable($db,'sys_set')) {
      createSysSetTable($db);
    }
    $result = $db->getOneRecord("SELECT val FROM sys_set WHERE sskey='{$key}' ");
    if($result) {
      return $result['val'];
    }else{
      return '';
    }
  }

  function setSysSet($key, $val) {
    $db = new DbHandler();
    $session = $db->getSession();
    if(!checkTable($db,'sys_set')) {
      createSysSetTable($db);      
    }
    $result = $db->getInsert("INSERT INTO sys_set SET sskey = '{$key}', val = '{$val}', note = '', update_date = localtimestamp, update_by = '{$session['name']}' ON DUPLICATE KEY UPDATE val = '{$val}' ");

    return $result;
  }

  function createActionTable($db) {
    /*
      硬體指令發送
      status: 0-未處理、1-已處理 
      actionCode: PHP執行最後產生的command hex
      createdAt: 建立日期
    */
    $createTable = $db->getQuery("CREATE TABLE IF NOT EXISTS device_action (
			devuid int(11) NOT NULL AUTO_INCREMENT,
			rscuid int(11) DEFAULT '0',
			rmid varchar(100) COLLATE utf8_unicode_ci NOT NULL,
			rmip varchar(50) COLLATE utf8_unicode_ci NOT NULL,
			dev_status int(11) NOT NULL DEFAULT '0',
			file_path varchar(100) COLLATE utf8_unicode_ci NOT NULL,
			action_code varchar(100) COLLATE utf8_unicode_ci NOT NULL,
			note varchar(1000) COLLATE utf8_unicode_ci NOT NULL,
			create_date timestamp NULL DEFAULT CURRENT_TIMESTAMP,
			create_by varchar(50) COLLATE utf8_unicode_ci DEFAULT 'admin',
			PRIMARY KEY (devuid),
			KEY idx_dev_devuid (devuid) USING BTREE,
			KEY idx_dev_rmid_status (rmid,dev_status) USING BTREE
		  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; ");

}

function createSysSetTable($db) {
  /*
    系統設定
    createdAt: 建立日期
  */
  $createTable = $db->getQuery("xxxx");
  // CREATE TABLE IF NOT EXISTS public.sys_set(
  //   syssetuid serial NOT NULL,
  //   sskey varchar(100) NOT NULL,
  //   val varchar(100)  NOT NULL DEFAULT ''::character varying,
  //   note varchar(1000) NULL,
  //   create_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  //   create_by varchar(50) NOT NULL DEFAULT 'admin'::character varying,
  //   update_date timestamp NULL,
  //   update_by varchar(50) NULL,
  //   CONSTRAINT sys_set_pkey PRIMARY KEY (syssetuid),
  //   CONSTRAINT sys_set_sskey_key UNIQUE (sskey)
  //); 
  
 // $createIndex = $db->getQuery("CREATE INDEX idx_sysset_sskey ON public.sys_set USING btree (sskey);");
}
?>