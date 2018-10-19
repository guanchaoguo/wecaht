<?php
require 'jspay.php';

$appid = "wxbf70834035c36c2d";
$appsecret ="03cb691a08a6d64f79c54e4779f8c5c6";
$mchid ='1422062802';
$key ='wxs1000000OOOOOOOOOOooooooooooQQ';
session_start();

$pay = new jspay($mchid, $appid, $key);

$op_user_id = 'sys';
$out_refund_no = $mchid.date("YmdHis").'666';
$out_trade_no = '142206280220170503032106';
$refund_fee = '0.01';
$total_fee = '0.01';



$_SESSION['refund'] = $pay->refund($op_user_id, $out_refund_no, $out_trade_no, $refund_fee, $total_fee, $key);
file_put_contents('refund.text', json_encode($_SESSION['refund']));
var_dump($_SESSION);
