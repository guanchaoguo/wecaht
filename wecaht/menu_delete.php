<?php

header('Content-Type: text/html; charset=utf-8');

define('APPID', 'wxe647b92b9dc39f1b');
define('APPSECRET', 'e16374d53b5b78bf25fd7e29e3d58c46');
define('TOKEN', 'itcast_php_php35');

require './WeChat.class.php';

$wechat = new WeChat(APPID, APPSECRET, TOKEN);


$result = $wechat->menuDelete();

echo $result;
// var_dump($result);