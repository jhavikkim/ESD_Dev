<?php

class DbHandler {

  private $conn;

  function __construct() {
    require_once 'dbConnect.php';
    // opening db connection
    $db = new dbConnect();
    $this->conn = $db->connect();
  }
  /**
   * Fetching single record
   */
  public function getOneRecord($query) {
    //echo $query;
    $r = $this->conn->query($query.' LIMIT 1 ') or die($this->conn->error.__LINE__);
    return $result = $r->fetch_assoc();
  }

  public function getQuery($query) {
    //echo $query;
    $r = $this->conn->query($query) or die($this->conn->error.__LINE__);
    return $result = $r;
  } 

  public function getInsert($query) {
    //echo $query;
    $r = $this->conn->query($query) or die($this->conn->error.__LINE__);
    return $result = $r;
  } 

  public function getUpdate($query) {
    //echo $query;
    $r = $this->conn->query($query) or die($this->conn->error.__LINE__);
    return $result = $r;
  } 

  public function getSession(){
    if (!isset($_SESSION)) {
      session_start();
    }
    $sess = array();
    if(isset($_SESSION['uid'])) {
      $sess["uid"] = $_SESSION['uid'];
      $sess["name"] = $_SESSION['name'];
      $sess["email"] = $_SESSION['email'];
      $sess["auth"] = $_SESSION['auth'];
    }else{
      $sess["uid"] = '';
      $sess["name"] = '訪客';
      $sess["email"] = '';
      $sess["auth"] = '';
    }
    return $sess;
  }

  public function destroySession(){
    if (!isset($_SESSION)) {
      session_start();
    }
    if(isSet($_SESSION['uid'])) {
      unset($_SESSION['uid']);
      unset($_SESSION['name']);
      unset($_SESSION['email']);
      unset($_SESSION['auth']);
      $info='info';
      if(isSet($_COOKIE[$info])) {
        setcookie ($info, '', time() - $cookie_time);
      }
      $msg = "登出成功";
    }else{
      $msg = "未登入";
    }
    return $msg;
  }
 
}

?>