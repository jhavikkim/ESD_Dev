<?php

$app->post('/userList', function($request, $response, $args){
  $db = new DbHandler();

  $session = $db->getSession();
  if (!isset($session['uid'])){
    $return["status"] = "錯誤";
    $return["message"] = "未登入狀態";
    return $response->withJson($return, 201);
  }else{
    $login_auth = $session["auth"];
    $result = $db->getQuery("SELECT accuid, display_name, `user_name`, email, phone, auth, create_date FROM accounts_infos  WHERE auth<3");
    
    $return = array();
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $obj = new StdClass();
        $obj->uid = (int)$row["accuid"];
        $obj->name = $row["display_name"];
        $obj->userID = $row["user_name"];
        $obj->email = $row["email"];
        $obj->phone = $row["phone"];
        $obj->auth = $row["auth"];
        $obj->created = $row["create_date"];
        switch ((int)$row["auth"])
        {
          case 0:
            $obj->authStr = "停用帳號";
            break;
          case 1:
            $obj->authStr = "一般帳號";
            break;
          case 2:
            $obj->authStr = "管理帳號";
            break;
          default:
            break;
        }
        array_push($return, $obj);
      }
    }
    return $response->withJson($return, 200);
  }
});

$app->post('/userContent', function($request, $response, $args){
  $r = json_decode($request->getBody());
  $db = new DbHandler();

  $session = $db->getSession();
  $uid = $r->uid;
  if (!isset($session['uid'])){
    $return["status"] = "錯誤";
    $return["message"] = "未登入狀態";
    return $response->withJson($return, 201);
  }else{
    $login_auth = $session["auth"];
    $result = $db->getQuery("SELECT accuid, display_name, `user_name`, email, phone, auth, create_date FROM accounts_infos WHERE accounts_infos.accuid = '$uid'; ");

    $obj = new StdClass();
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $obj->uid = (int)$row["accuid"];
        $obj->name = $row["display_name"];
        $obj->userID = $row["user_name"];
        $obj->email = $row["email"];
        $obj->phone = $row["phone"];
        $obj->auth = $row["auth"];
        $obj->created = $row["create_date"];
      }
    }
    return $response->withJson($obj, 200);
  }
});

$app->post('/modifyUser', function($request, $response, $args){
  $r = json_decode($request->getBody());
  $db = new DbHandler();

  $session = $db->getSession();
  $uid = $r->uid;
  $name = $r->name;
  $userID = $r->userID;
  $email = $r->email;
  $auth = (int)$r->auth;
  $phone = $r->phone;

  if (!isset($session['uid'])){
    $return["status"] = "錯誤";
    $return["message"] = "未登入狀態";
    return $response->withJson($return, 201);
  }else{
    $login_auth = $session["auth"];

    $update = $db->getUpdate("UPDATE accounts_infos SET display_name='$name', `user_name` = '$userID' ,email='$email', auth='$auth', phone='$phone' WHERE accuid='$uid'");

    $obj = new StdClass();
    $obj->data = 'ok';
    return $response->withJson($r, 200);
  }
});


$app->post('/addUser', function($request, $response, $args){
  $r = json_decode($request->getBody());
  $db = new DbHandler();

  $session = $db->getSession();

  $name = $r->name;
  $userID = $r->userID;
  $email = $r->email;
  $auth = (int)$r->auth;
  $phone = $r->phone;
  $password = $r->password;

  $db->getInsert("INSERT INTO accounts_infos (display_name, user_name, email, phone, password, auth) VALUES ('$name', '$userID', '$email', '$phone', '$password', '$auth')");

  return '';
});

?>