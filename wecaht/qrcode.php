<?php
header('Content-Type: text/html; charset=utf-8');

define('APPID', 'wxe647b92b9dc39f1b');
define('APPSECRET', 'e16374d53b5b78bf25fd7e29e3d58c46');
define('TOKEN', 'itcast_php_php35');

require './WeChat.class.php';

$wechat = new WeChat(APPID, APPSECRET, TOKEN);

// 生成QRCode
$image_content = $wechat->getQRCode(42);
// 存储成图片
file_put_contents('./qrcode.jpg', $image_content);
// 输出到浏览器显示
header('Content-Type: image/jpeg');
echo $image_content;