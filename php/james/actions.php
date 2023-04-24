<?php
  $app->get("/spaceCheck", function($request, $response, $args){
    $bytes = disk_free_space("D:");
    $si_prefix = array( 'B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB' );
    $base = 1024;
    $class = min((int)log($bytes , $base) , count($si_prefix) - 1);
    $space = sprintf('%1.2f' , $bytes / pow($base,$class)).' '.$si_prefix[$class];

    return $response->withJson($space , 200);
  });
  $app->get("/spaceCheckByte", function($request, $response, $args){
    $useOverwrite = (getSysSet("useOverwrite")=="1");
    $hdlimit = (getSysSet("hdlimit")=="")?10.0:floatval(getSysSet("hdlimit"));
    $bytes = disk_free_space("D:");
    $hdGB = $bytes / 1024 / 1024 / 1024;

    //return $response->withJson($hdGB, 200);
    return $response->withJson(($useOverwrite && ($hdGB < $hdlimit)), 200);
  });
  $app->get("/processAction", function($request, $response, $args) {
    $rmID = $request->getParam('rmID');
    return getRMAction($rmID);
  });

  $app->get('/playsound', function($request, $response, $args) {
    return $response->withJson(playsound(), 200);
  });

  $app->post('/killplaysound', function($request, $response, $args) {
    setSysSet('psSoundAlarm', '');
    fopen(dirname(__DIR__,2)."\\python\\STOPAUDIO", "w");
  });

  $app->get('/deleteLastOne', function($request, $response, $args) {
    deleteLastOneFull();
    return $response->withJson('', 200);
  });

  $app->get('/getSysSet',function($request, $response, $args){
    return $response->withJson(getSysSet($request->getParam('key')), 200);
  });

  $app->post('/setSysSet',function($request, $response, $args){
    $r = json_decode($request->getBody());
    $key = $r->key;
    $val = $r->val;
    return $response->withJson(setSysSet($key, $val), 200);
  });

  $app->post('/setRMval',function($request, $response, $args){
    $r = json_decode($request->getBody());
    
    $rmID = $r->rmID;
    $rmIP = $r->rmIP;
    
    setRMAction($rmIP, $r->uid, $rmID, "", "/RM/setAlarm.php?ch=".substr($r->ch,2,strlen($r->ch))."&max=".$r->max."&min=".$r->min, $note = '設定警戒值');
  });

?>