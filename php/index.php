<?php 
header ('Content-Type: text/html; charset=utf-8');

require_once 'dbHandler.php';
require_once 'auth/passwordHash.php';
require 'libs/vendor/autoload.php';

$app = new \Slim\App;

require_once 'config.php';

require_once 'auth/authentication.php';
require_once 'monitor/dashboard.php';
require_once 'monitor/view.php';
require_once 'monitor/fan.php';
require_once 'monitor/rmg.php';
require_once 'getCurrentValue.php';
require_once 'setting/list.php';
require_once 'user/user.php';
require_once 'james/functions.php';
require_once 'james/actions.php';
require_once 'james/alarm.php';
// require_once 'test/test.php';

$app->run();

?>