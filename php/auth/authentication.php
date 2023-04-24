<?php

$app->get('/reflashTime', function($request, $response, $args) {
  $db = new DbHandler();
  $data = $db->getOneRecord("SELECT syssetuid, val FROM sys_set WHERE sskey = 'refreshTime'");
  $return["uid"] = $data['syssetuid'];
  $return["times"] = $data['val'];
  return $response->withJson($return, 200);
});

$app->post('/setRefleshTimes', function($request, $response, $args) {
  $db = new DbHandler();
  $session = $db->getSession();
  $r = json_decode($request->getBody());
  $times = $r->times;
  $data = $db->getUpdate("UPDATE sys_set SET val='$times', update_date = LOCALTIMESTAMP, update_by = '{$session['name']}' WHERE sskey = 'refreshTime'");
  
  return $response->withJson($times, 200);
});

$app->get('/session', function($request, $response, $args) {
  $db = new DbHandler();
  $session = $db->getSession();
  $return["uid"] = $session['uid'];
  //$return["username"] = $session['username'];
  $return["email"] = $session['email'];
  $return["name"] = $session['name'];
  $return["auth"] = $session['auth'];
  return $response->withJson($return, 200);
});

$app->post('/login', function($request, $response, $args) {
  require_once 'passwordHash.php';
  $r = json_decode($request->getBody());
  $return = array();
  $db = new DbHandler();
  $password = $r->credentials->password;
  $email = $r->credentials->userID;
  $user = $db->getOneRecord("SELECT accuid, display_name, user_name, password, email, create_date, auth 
                              FROM accounts_infos WHERE (email='$email' or user_name ='$email')  AND auth<>0 ");
  if ($user != NULL) {
    if(passwordHash::check_password(hash('sha512', $user['password']), $password)) {
      $return['status'] = "成功"; 
      $return['message'] = '登入成功';
      $return['name'] = $user['display_name'];
      $return['uid'] = $user['accuid'];
      $return['username'] = $user['user_name'];
      $return['email'] = $user['email'];
      $return['auth'] = $user['auth'];
      $return['createdAt'] = $user['create_date'];
      if (!isset($_SESSION)) {
        session_start();
      }
      $_SESSION['uid'] = $user['accuid'];
      $_SESSION['email'] = $email;
      $_SESSION['username'] = $user['user_name'];
      $_SESSION['name'] = $user['display_name'];
      $_SESSION['auth'] = $user['auth'];
    }else{
      $return['status'] = "錯誤";
      $return['message'] = '登入失敗，密碼不正確';
    }
  }else{
    $return['status'] = "錯誤";
    $return['message'] = '此用戶未註冊';
  }
  return $response->withJson($return, 200);
});

$app->get('/logout', function($request, $response, $args) {
  $db = new DbHandler();
  $session = $db->destroySession();
  $return["status"] = "資訊";
  $return["message"] = "登出成功";
  return $response->withJson($return, 200);
});

$app->post('/changePassword', function($request, $response, $args) {
  $r = json_decode($request->getBody());
  $return = array();
  $db = new DbHandler();
  $session = $db->getSession();
  if (!isset($session['uid'])){
    $return["status"] = "錯誤";
    $return["message"] = "未登入狀態";
    return $response->withJson($return, 201);
  }else{
    $uid = $session['uid'];
    $oldPassword = $r->oldPassword;
    $newPassword = $r->newPassword;
    $rePassword = $r->rePassword;
    $user = $db->getOneRecord("SELECT accuid FROM accounts_infos WHERE accuid='$uid' AND password='$oldPassword' ");
    if ($user != NULL) {
      if ($newPassword === $rePassword) {
        $changePassword = $db->getUpdate("UPDATE accounts_infos SET password='$newPassword', update_date = LOCALTIMESTAMP, update_by = '{$session['name']}' WHERE accuid='$uid'");
        $return["status"] = "成功";
        $return["message"] = "密碼修改成功";
        return $response->withJson($return, 200);
      }else{
        $return["status"] = "錯誤";
        $return["message"] = "新密碼設定錯誤";
        return $response->withJson($return, 201);
      }
    }else{
      $return["status"] = "錯誤";
      $return["message"] = "舊密碼錯誤";
      return $response->withJson($return, 201);
    }
  }
});

$app->post('/resetPassword', function($request, $response, $args) {
  $r = json_decode($request->getBody());
  $return = array();
  $db = new DbHandler();
  $session = $db->getSession();
  if (!isset($session['uid'])){
    $return["status"] = "錯誤";
    $return["message"] = "未登入狀態";
    return $response->withJson($return, 201);
  }else{
    $uid = $r->uid;
    $resetPassword = $db->getUpdate("UPDATE accounts_infos SET password='00000000' WHERE accuid='$uid'");
    return $response->withJson($return, 201);
  }
});

$app->post('/deleteUser', function($request, $response, $args) {
  $r = json_decode($request->getBody());
  $return = array();
  $db = new DbHandler();
  $session = $db->getSession();
  if (!isset($session['uid'])){
    $return["status"] = "錯誤";
    $return["message"] = "未登入狀態";
    return $response->withJson($return, 201);
  }else{
    $uid = $r->uid;
    $deleteUser = $db->getUpdate("DELETE FROM accounts_infos WHERE accuid='$uid'");
    return $response->withJson($return, 201);
  }
});

?>