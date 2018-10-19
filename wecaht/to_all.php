<?php


header('Content-Type: text/html; charset=utf-8');

define('APPID', 'wxe647b92b9dc39f1b');
define('APPSECRET', 'e16374d53b5b78bf25fd7e29e3d58c46');
define('TOKEN', 'itcast_php_php35');

require './WeChat.class.php';

$wechat = new WeChat(APPID, APPSECRET, TOKEN);


// $content = 'Bye, Bye!';
$content = '我们毕业啦';
$user_list = array(
	'oBtHYsgB4lD5AqN4OpFbd_PrVxYI',
	'oBtHYskuMS1wnPxcGkrIqm71WE2U',
	'oBtHYsnBM7NYwhMLRvwtqzXq5Ib4',
	'oBtHYsuzWpaKcsD2xeCw7U9EjkFw',
	'oBtHYsiWyc_tH2DBQWU47Q7TNbiw'
	);
$result = $wechat->responseAll($content, $user_list);

var_dump($result);