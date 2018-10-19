<?php
echo __DIR__.'/apiclient_cert.pem';die;
/**
  * OAuth2.0微信授权登录实现
  *
  * @author zzy
  * @文件名：GetWxUserInfo.php
  */

    require 'jspay.php';
   // 回调地址
   $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
   $redirect_uri = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
   // echo $redirect_uri;die;
   $url = urlencode($redirect_uri);
    // 公众号的id和secret
    $appid = "wx025bc3f126d658b3";
    $appsecret ="b0622f02859ae2187f0c886bff1c68f2";
    session_start();
    // $_SESSION = [];

$appid = "wxbf70834035c36c2d";
$appsecret ="03cb691a08a6d64f79c54e4779f8c5c6";
$mchid ='1422062802';
$key ='wxs1000000OOOOOOOOOOooooooooooQQ';
     // 获取code码，用于和微信服务器申请token。 注：依据OAuth2.0要求，此处授权登录需要用户端操作
     if (!isset($_GET['code']) && !isset($_SESSION['code'])) {
         $codeUrl ='https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=snsapi_base&state=1#wechat_redirect';
         $codeUrl = sprintf($codeUrl, $appid, $redirect_uri);

         $href = '<script type="text/javascript"> window.location.href="%s"</script>';
         $href = sprintf($href, $codeUrl);
         echo $href;
         exit;
     }

     // 依据code码去获取openid和access_token，自己的后台服务器直接向微信服务器申请即可
     if (isset($_GET['code']) && !isset($_SESSION['token'])) {
         $_SESSION['code'] = $_GET['code'];

         $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=%s&secret=%s&code=%s&grant_type=authorization_code';
         $url = sprintf($url, $appid, $appsecret, $_GET['code']);
         $res = https_request($url);
         $res=(json_decode($res, true));
         $_SESSION['token'] = $res;
     }

    // var_dump($_SESSION);

    /* // 依据申请到的access_token和openid，申请Userinfo信息。
     if (isset($_SESSION['token']['access_token'])) {
         $url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$_SESSION['token']['access_token']."&openid=".$_SESSION['token']['openid']."&lang=zh_CN";
         echo $url;
         $res = https_request($url);
         $res = json_decode($res, true);

         $_SESSION['userinfo'] = $res;
     }

    var_dump($_SESSION);*/

 // cURL函数简单封装
 function https_request($url, $data = null)
 {
     $curl = curl_init();
     curl_setopt($curl, CURLOPT_URL, $url);
     curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
     curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
     if (!empty($data)) {
         curl_setopt($curl, CURLOPT_POST, 1);
         curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
     }
     curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
     $output = curl_exec($curl);
     curl_close($curl);
     return $output;
 }

   /* // 地理位置获取
    function createNonceStr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    // 获取 access_token
     if (!isset($_SESSION['access_token'])) {
         $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$appsecret}";
         $res = https_request($url);
         $res=(json_decode($res, true));
         $_SESSION['access_token'] = $res['access_token'];
     }

    // 获取jsapi_ticket
    if (!isset($_SESSION['jsapi_ticket'])) {
        $accessToken =  $_SESSION['access_token'];
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token={$accessToken}";
        $res = https_request($url);
        $res = json_decode($res, true);
        $_SESSION['jsapi_ticket'] = $res['ticket'];
    }*/

    // 生成签名
  /*  $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $timestamp = time();
    $nonceStr = createNonceStr();
    $jsapiTicket = $_SESSION['jsapi_ticket'];
    $string = "jsapi_ticket={$jsapiTicket}&noncestr={$nonceStr}&timestamp={$timestamp}&url={$url}";// 按照key值 ASCII码升序排序
    $signature = sha1($string);
    $signPackage = array(
      "appId"     => $appid,
      "nonceStr"  => $nonceStr,
      "timestamp" => $timestamp,
      "url"       => $url,
      "signature" => $signature,
      "rawString" => $string
    );*/

    // 统一下单
    $openid = $_SESSION['token']['openid'];
    $totalFee ='0.01';
    $outTradeNo = $mchid.date("YmdHis");
    $orderName = 'test';
    $notifyUrl= 'http://testwchat.tunnel.qydev.com/wecaht/notify.php';
    $timestamp = time();

    $pay = new jspay($mchid, $appid, $key);
    $order = $pay->createJsBizPackage($openid, $totalFee, $outTradeNo, $orderName, $notifyUrl, $timestamp);
    $jsApiParameters = json_encode($order);
    $_SESSION['order'] = $order;
    $_SESSION['outTradeNo'] = $outTradeNo;
    file_put_contents('order.text', json_encode($_SESSION));
    echo json_encode([200,'支付签名','data'=>$order]);
    die;


?>

<!-- <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title></title>
</head>
<body>

</body>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script src="http://libs.baidu.com/jquery/1.9.1/jquery.min.js"></script>
<script>
  /*
   * 注意：
   * 1. 所有的JS接口只能在公众号绑定的域名下调用，公众号开发者需要先登录微信公众平台进入“公众号设置”的“功能设置”里填写“JS接口安全域名”。
   * 2. 如果发现在 Android 不能分享自定义内容，请到官网下载最新的包覆盖安装，Android 自定义分享接口需升级至 6.0.2.58 版本及以上。
   * 3. 常见问题及完整 JS-SDK 文档地址：http://mp.weixin.qq.com/wiki/7/aaa137b55fb2e0456bf8dd9148dd613f.html
   *
   * 开发中遇到问题详见文档“附录5-常见错误及解决办法”解决，如仍未能解决可通过以下渠道反馈：
   * 邮箱地址：weixin-open@qq.com
   * 邮件主题：【微信JS-SDK反馈】具体问题
   * 邮件内容说明：用简明的语言描述问题所在，并交代清楚遇到该问题的场景，可附上截屏图片，微信团队会尽快处理你的反馈。
   */
  wx.config({
    debug: true,
    appId: '<?php echo $signPackage["appId"];?>',
    timestamp: <?php echo $signPackage["timestamp"];?>,
    nonceStr: '<?php echo $signPackage["nonceStr"];?>',
    signature: '<?php echo $signPackage["signature"];?>',
    jsApiList: [
      'getLocation'
    ]
  });
  wx.ready(function () {
    wx.chooseWXPay({
    timestamp: <?php echo $order["timeStamp"];?>, // 支付签名时间戳，注意微信jssdk中的所有使用timestamp字段均为小写。但最新版的支付后台生成签名使用的timeStamp字段名需大写其中的S字符
    nonceStr: '<?php echo $order["nonceStr"];?>', // 支付签名随机串，不长于 32 位
    package: '<?php echo $order["package"];?>', // 统一支付接口返回的prepay_id参数值，提交格式如：prepay_id=***）
    signType: '<?php echo $order["signType"];?>', // 签名方式，默认为'SHA1'，使用新版支付需传入'MD5'
    paySign: '<?php echo $order["paySign"];?>', // 支付签名
    success: function (res) {
      alert((res);
        // 支付成功后的回调函数
    }
});

  });

  wx.getLocation({
        success: function (res) {
            var latitude = res.latitude; // 纬度，浮点数，范围为90 ~ -90
            var longitude = res.longitude; // 经度，浮点数，范围为180 ~ -180。
            var speed = res.speed; // 速度，以米/每秒计
            var accuracy = res.accuracy; // 位置精度
            $.get('/test1.php?x='+latitude+'&y='+longitude, function(result){
              alert(result);
          });
        },
        cancel: function (res) {
            alert('用户拒绝授权获取地理位置');
        }

  });
</script>
</html> -->





