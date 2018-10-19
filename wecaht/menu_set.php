<?php

header('Content-Type: text/html; charset=utf-8');

define('APPID', 'wxe647b92b9dc39f1b');
define('APPSECRET', 'e16374d53b5b78bf25fd7e29e3d58c46');
define('TOKEN', 'itcast_php_php35');

require './WeChat.class.php';

$wechat = new WeChat(APPID, APPSECRET, TOKEN);



$menu = <<<JSON
{
   "button":[
   {
        "type":"click",
        "name":"最新消息",
        "key":"NEWS"
    },
    {
        "type":"click",
        "name":"整点音乐",
        "key":"COME_SOME_MUSIC"
    },
    {
         "name":"微信资源",
         "sub_button":[
         {
             "type":"view",
             "name":"搜索",
             "url":"http://www.soso.com/"
          },
          {
             "type":"view",
             "name":"视频",
             "url":"http://v.qq.com/"
          },
          {
             "type":"click",
             "name":"赞一下我们",
             "key":"V1001_GOOD"
          }]
     }]
}
JSON
;

$result = $wechat->menuSet($menu);
var_dump($result);