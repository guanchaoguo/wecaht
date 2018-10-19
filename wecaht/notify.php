<?php
require 'jspay.php';

$appid = "wxbf70834035c36c2d";
$appsecret ="03cb691a08a6d64f79c54e4779f8c5c6";
$mchid ='1422062802';
$key ='wxs1000000OOOOOOOOOOooooooooooQQ';
session_start();

$pay = new jspay($mchid, $appid, $key);
$_SESSION['notify'] = $pay->notify();
file_put_contents('notify.text', json_encode($_SESSION['notify']));
var_dump($_SESSION);
