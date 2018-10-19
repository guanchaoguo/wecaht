<?php

/**
 * PHP操作微信公众平台接口 类
 */

class WeChat
{
    public $_appid;
    public $_appsecret;
    public $_token;

    public function __construct($appid, $appsecret, $token)
    {
        $this->_appid = $appid;
        $this->_appsecret = $appsecret;
        $this->_token = $token;
    }

    /**
     * 处理分析接收到的消息数据
     */
    public function doRequest()
    {
        // 接收请求数据
        // 不是典型 的KEY/Value形式的请求主体数据，可以使用下面的元素来获取到

        $xml_str = $GLOBALS['HTTP_RAW_POST_DATA'];
        // 使用simpleXML进行处理
        // 安全考虑，不去解析外部的XML实体，防止xml注入
        libxml_disable_entity_loader(true);
        $msg = simplexml_load_string($xml_str, 'SimpleXMLElement', LIBXML_NOCDATA);
        // 针对于不同的消息类型做不同的处理方法
        switch ($msg->MsgType) {
            case 'event':
                // 判断事件类型
                if ($msg->Event == 'subscribe') {
                    // 订阅（关注）事件
                    $this->_dosubScribe($msg);//调用该函数处理订阅事件
                }
                // 菜单点击事件
                elseif ($msg->Event == 'CLICK') {
                    $this->_doMenuClick($msg);
                }
                break;
            case 'text':
                $this->_doText($msg);
                break;
            case 'image':
                $this->_doImage($msg);
                break;
            case 'location':
                $this->_doLocation($msg);
                break;
        }
    }
    // 响应发送数据模板
    public $_template = array(
        'text' => <<<XML
<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>
XML
,
        'image' => <<<XML
<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[image]]></MsgType>
<Image>
<MediaId><![CDATA[%s]]></MediaId>
</Image>
</xml>
XML
,
        'music' => <<<XML
<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[music]]></MsgType>
<Music>
<Title><![CDATA[%s]]></Title>
<Description><![CDATA[%s]]></Description>
<MusicUrl><![CDATA[%s]]></MusicUrl>
<HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
<ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
</Music>
</xml>
XML
,
        'news' => <<<XML
<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>%s</ArticleCount>
<Articles>
%s
</Articles>
</xml>
XML
,
        'news_item' => <<<XML
<item>
<Title><![CDATA[%s]]></Title>
<Description><![CDATA[%s]]></Description>
<PicUrl><![CDATA[%s]]></PicUrl>
<Url><![CDATA[%s]]></Url>
</item>
XML
,
        );

    /**
     * 菜单点击事件
     */
    public function _doMenuClick($msg)
    {
        // 判断当前的菜单key
        switch ($msg->EventKey) {
            case 'NEWS':
                $this->_responseNews($msg);
                break;
            case 'COME_SOME_MUSIC':
                $this->_responseMusic($msg);
                break;
        }
    }
    /**
     * 向目标用户群发消息
     * @param  [type] $content   [description]
     * @param  [type] $user_list [description]
     * @return [type]            [description]
     */
    public function responseAll($content, $user_list)
    {
        $api_url = 'https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=' . $this->_getAccessToken();

        // $data['touser'] = $user_list;
        // $data['msgtype'] = 'text';
        // $data['text']['content'] = $content;
        // $data = json_encode($data);//, JSON_UNESCAPED_UNICODE);//>PHP5.4
        $user_list_string = json_encode($user_list);
        $data = <<<JSON
{
	"touser": $user_list_string,
    "msgtype": "text",
    "text": {
    	"content": "$content"
 		}
}
JSON
;
        // file_put_contents('./test', $data);
        return $this->_POST($api_url, $data);
    }

    /**
     * 菜单删除
     */
    public function menuDelete()
    {
        $api_url = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=' . $this->_getAccessToken();
        return $this->_GET($api_url);
    }
    /**
     * 设置菜单
     */
    public function menuSet($menu)
    {
        $api_url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $this->_getAccessToken();

        return $response_content = $this->_POST($api_url, $menu);
    }

    /**
     * 获取用户
     */
    public function getUserList()
    {
        $api_url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$this->_getAccessToken();

        $response_content = $this->_GET($api_url);
        return $response_content;
    }

    /**
     * 处理位置信息
     */
    public function _doLocation($msg)
    {
        // 直接响应经纬度
        // $content = '你所处的位置:' . "\n" . '纬度：' . $msg->Location_X . "\n" . '经度:'  . $msg->Location_Y;
        // 利用百度地图平台API，通过经纬度，做一些查询操作
        // 检索圆形范围内的数据
        $query = '银行';
        $location = $msg->Location_X . ',' . $msg->Location_Y;
        $radius = 1500;
        $output = 'json';
        $page_size = 5;
        $ak = 'OBDl6igKrng0V6VqT5RWIP6z';

        $map_api_url = 'http://api.map.baidu.com/place/v2/search?query=' . urlencode($query) . '&location=' . $location . '&radius=' . $radius . '&output='. $output . '&page_size=' . $page_size . '&ak=' . $ak;
        // 请求
        $response_content = $this->_GET($map_api_url);

        // 获取到所有的地名和位置：
        // $content = '附近的' . $query . '为：' . "\n";
        $response_data = json_decode($response_content);
        // foreach($response_data->results as $result) {
        // 	$content .= $result->name . ', 位于：' . $result->address . "\n";
        // }
        // file_put_contents('./result', $map_api_url . "\n" . $response_content);
        // 找到的位置，将位置图片响应给微信用户
        foreach ($response_data->results as $result) {
            $markers_list[] = $result->location->lng . ',' . $result->location->lat;
        }
        $markers = implode('|', $markers_list);
        $center = $msg->Location_Y . ',' . $msg->Location_X;
        $width = 280;
        $height = 320;
        $zoom = 16;
        $scale = 2;

        $map_staticimage = "http://api.map.baidu.com/staticimage?center=$center&width=$width&height=$height&zoom=$zoom&markers=$markers&scale=$scale";
        // 获取该图片
        $image_content = $this->_GET($map_staticimage, false);
        // 存储到web服务器上
        $file = './' . uniqid() . '.png';
        file_put_contents($file, $image_content);

        //上传到微信服务器上
        $upload_result = $this->_uploadMedia($file, 'image');
        // 删除临时在web服务器上的图片
        @unlink($file);
        // file_put_contents('./upload_result', $upload_result);

        // 做响应
        $upload_data = json_decode($upload_result);
        $media_id = $upload_data->media_id;
        $template = $this->_template['image'];
        $response_content = sprintf($template, $msg->FromUserName, $msg->ToUserName, time(), $media_id);
        echo $response_content;
    }

    public function _uploadMedia($file, $type)
    {
        $api_url = 'https://api.weixin.qq.com/cgi-bin/media/upload?access_token='.$this->_getAccessToken().'&type='.$type;
        $data['media'] = '@'.$file;
        $response_content = $this->_POST($api_url, $data);
        return $response_content;
    }
    /**
     * 处理图片类型的消息
     */
    public function _doImage($msg)
    {
        $content = '你所上传的图片的URL地址为: ' . $msg->PicUrl;
        // 做响应
        $template = $this->_template['text'];
        $response_content = sprintf($template, $msg->FromUserName, $msg->ToUserName, time(), $content);
        echo $response_content;
        file_put_contents('./media_id.txt', $msg->MediaId);
    }
    /**
     * 处理文本消息
     */
    public function _doText($msg)
    {
        // 具体的响应数据，依赖于数据库中存储的信息
        $content_default = '输入下面的内容，获取索要的信息' . "\n";
        $content_default .= '<PHP> 获取PHP开班信息' . "\n";
        $content_default .= '<Java> 获取PHP开班信息' . "\n";
        $content_default .= '<IOS> 获取PHP开班信息' . "\n";
        switch (strtoupper($msg->Content)) {
            case 'PHP':
                $content = 'PHP, 世界上最流行的WEB编程语言';
                break;
            case 'Java':
                $content = 'Java, 是一种可以撰写跨平台应用软件的面向对象的程序设计语言';
                break;
            case 'IOS':
                $content = 'PHP, 移动端开发利器，高大上的编程语言';
                break;
            case '音乐':
            case 'MUSIC':
                $this->_responseMusic($msg);
                break;
            case '新闻':
            case 'NEWS':
                $this->_responseNews($msg);
                break;
            case '?':
            case 'HELP':
            case '帮助':
                $content = $content_default;
                break;
            default:
                // 利用小黄鸡，完成智能聊天
                $url = "http://www.xiaohuangji.com/ajax.php";
                // post请求
                $data['para'] = $msg->Content;
                $content = $this->_POST($url, $data, false);
        }

        // 做响应
        $template = $this->_template['text'];
        $response_content = sprintf($template, $msg->FromUserName, $msg->ToUserName, time(), $content);
        echo $response_content;
    }
    /**
     * 响应为新闻类型
     * @param  [type] $msg [description]
     * @return [type]      [description]
     */
    public function _responseNews($msg)
    {
        // 获取的最新的新闻列表
        $news_list = array(
            array(
                'title'=>'伟大的公司不会热衷私有化和A股游戏',
                'description'=>'经纬中国管理合伙人张颖在纳斯达克交易大厅里一起竖起中指的合影，现在看上去好像是在嘲笑这场虚无的盛宴',
                'pic_url'=>'http://101.200.230.84/1.gif',
                'url'=>'http://www.pingwest.com/great-companies-will-never-enjoy-the-a-share-game/',
                ),
            array(
                'title'=>'质疑互联网公司的正确姿势',
                'description'=>'应该如何质疑一家像乐视这样的互联网公司？关键看其六种能力：用户力，资本力，生态力，变现力，人才力，愿景力',
                'pic_url'=>'http://101.200.230.84/2.jpg',
                'url'=>'http://yinsheng.baijia.baidu.com/article/89045',
                ),
            array(
                'title'=>'微软缘何“杀死”诺基亚智能手表？',
                'description'=>'微软无论在智能手机市场的口碑还是实际的市场份额上，都没有可供智能手表借鉴的正面推动力。',
                'pic_url'=>'http://101.200.230.84/3.jpg',
                'url'=>'http://sunyongjie.baijia.baidu.com/article/88964',
                ),
            array(
                'title'=>'好想你募资逾8亿打造智慧门店与云商城平台',
                'description'=>'好想你(002582)今日公布非公开发行股票预案，公司拟以不低于29.9元/股的价格，向不超过10名特定对象发行2900万股，募集资金8.67亿元，用于智慧门店与云商城项目投资。',
                'pic_url'=>'http://101.200.230.84/4.jpg',
                'url'=>'http://stock.sohu.com/20150624/n415525016.shtml',
                ),
            );
        // 新闻列表
        $item_list = '';
        $template = $this->_template['news_item'];
        foreach ($news_list as $news) {
            $item_list .= sprintf($template, $news['title'], $news['description'], $news['pic_url'], $news['url']);
        }
        // 响应消息
        $template = $this->_template['news'];
        $response_content = sprintf($template, $msg->FromUserName, $msg->ToUserName, time(), count($news_list), $item_list);

        echo $response_content;
    }
    /**
     * 响应为音乐类型
     * @param  [type] $msg [description]
     * @return [type]      [description]
     */
    public function _responseMusic($msg)
    {
        // 存在几首音乐URL，响应即可！
        $music_list = array(
            array(
                'title' => '好久不见',
                'description'    => '作词：施立 作曲：陈小霞 演唱：陈奕迅',
                'url'    => 'http://yinyueshiting.baidu.com/data2/music/134369899/29276650400128.mp3?xcode=e1596c899d732aa30d9b8f0c677c76d2',
                ),
            array(
                'title' => '浮夸',
                'description'    => '作词：黄伟文 作曲：C.Y. Kong 演唱：陈奕迅',
                'url'    => 'http://yinyueshiting.baidu.com/data2/music/134371525/1000860216000128.mp3?xcode=770fd85def35e00b7ed60eb536e08c87',
                ),
            );


        // 随机选择一首音乐，响应给微信
        $rand_index = mt_rand(0, count($music_list)-1);
        $music = $music_list[$rand_index];
        // 做响应
        $template = $this->_template['music'];
        $response_content = sprintf($template, $msg->FromUserName, $msg->ToUserName, time(), $music['title'], $music['description'], $music['url'], $music['url'], 'RSkIHQhkDM4kYLhVwzcdKnVg3pt2ch29DoUB4PY4SqImgjVa4cDRyvWQlBeTd-xC');
        file_put_contents('./rc.txt', $response_content);
        echo $response_content;
    }
    /**
     * 用于处理订阅（关注）事件的方法
     */
    public function _dosubScribe($msg)
    {
        // 拼凑 符合文本信息的XML文档
        $template = $this->_template['text'];
        $content = '感谢关注！';
        $response_content = sprintf($template, $msg->FromUserName, $msg->ToUserName, time(), $content);
        echo $response_content;
    }

    /**
     * 第一次接入校验
     */
    public function firstCheck()
    {
        // 校验
        if ($this->_checkSignature()) {
            echo $_GET['echostr'];
        }
    }

    /**
     * 验证Signature,用于验证请求是否来源于微信服务器
     */
    public function _checkSignature()
    {
        // 排序需要加密的字符串
        $arr[] = $this->_token;
        $arr[] = $_GET['timestamp'];
        $arr[] = $_GET['nonce'];
        sort($arr, SORT_STRING);
        // 连接
        $arr_str = implode($arr);
        // 加密
        $sha1_str = sha1($arr_str);

        // 比较
        if ($sha1_str == $_GET['signature']) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 生成QRCODE
     *
     */
    public function getQRCode($scene_id, $type='QR_SCENE', $expire_seconds=604800)
    {
        // 获取access_token
        $access_token = $this->_getAccessToken();
        // 获取 ticket
        $ticket = $this->_getQRCodeTicket($scene_id, $type='QR_SCENE', $expire_seconds=604800);
        // 利用ticket换取图片内容
        $image_content = $this->_getQRCodeImage($ticket);
        return $image_content;
    }

    public function _getQRCodeImage($ticket)
    {
        $api_url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($ticket);
        $response_content = $this->_GET($api_url);
        return $response_content;
    }
    /**
     * [_getQRCodeTicket description]
     * @param int(string) $scene_id
     * @param [type] $type qrcode的类型
     * @param int $expire_seconds 临时二维码需要
     * @return [type]       [description]
     */
    public function _getQRCodeTicket($scene_id, $type='QR_SCENE', $expire_seconds=604800)
    {
        $api_url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $this->_getAccessToken();

        // Post 数据
        switch ($type) {// 判断类型
            case 'QR_LIMIT_SCENE':
                $data['action_name'] = 'QR_LIMIT_SCENE';
                $data['action_info']['scene']['scene_id'] = $scene_id;
                break;
            case 'QR_SCENE':
            default:
                $data['action_name'] = 'QR_SCENE';
                $data['action_info']['scene']['scene_id'] = $scene_id;
                $data['expire_seconds'] = $expire_seconds;
        }
        $data = json_encode($data);

        // 发请求获取ticket
        $response_content = $this->_POST($api_url, $data);
        $response_data = json_decode($response_content);
        if (isset($response_data->errcode)) {
            trigger_error('QRCode 的 Ticket 获取失败, 原因为' . $response_data->errmsg);
            return false;
        }
        return $response_data->ticket;
    }


    /**
     * 获取access_token
     */
    public function _getAccessToken()
    {
        // access_token缓存文件中地址
        $access_token_file = './access_token';
        if (file_exists($access_token_file)) {// 是否存在
            // 是否过期
            $content = file_get_contents($access_token_file);
            $data = explode('::', $content);
            if (time()-filemtime($access_token_file) <= $data[0]) {
                // 没有过期
                return $data[1];
            }
        }

        // 获取api请求地址
        $api_url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s';
        $api_url = sprintf($api_url, $this->_appid, $this->_appsecret);

        // get请求
        $response_content = $this->_GET($api_url);
        // 处理响应数据
        $response_data = json_decode($response_content);
        // 记录到access_token缓存文件中
        file_put_contents($access_token_file, $response_data->expires_in.'::'.$response_data->access_token);
        return $response_data->access_token;
    }



    /**
     * 发送GET请求
     * @param string $url URL
     * @param bool $https 是否为https请求
     * @return string 响应结果
     */
    public function _GET($url, $https=true)
    {
        return $this->_request($url);
    }

    /**
     * [_POST description]
     * @param  [type]  $url   [description]
     * @param  [type]  $data  [description]
     * @param  boolean $https [description]
     * @return [type]         [description]
     */
    public function _POST($url, $data, $https=true)
    {
        return $this->_request($url, $https, 'POST', $data);
    }


    /**
     * [_request description]
     * @param  [type]  $url   [description]
     * @param  boolean $https [description]
     * @param  string  $type  [description]
     * @param  array   $data  [description]
     * @return [type]         [description]
     */
    public function _request($url, $https=true, $type='GET', $data=null)
    {
        $curl = curl_init();

        // 设定选项
        curl_setopt($curl, CURLOPT_URL, $url);
        // 请求时通常会携带选项，代理信息，referer来源信息
        // 请求代理信息
        $useragent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36';
        curl_setopt($curl, CURLOPT_USERAGENT, $useragent);
        // 自动生成请求来源
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        // 请求超时时间
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        // 是否获取响应头
        curl_setopt($curl, CURLOPT_HEADER, false);
        // 是否返回响应结果
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if ($https) {// 是HTTPS请求
            // https相关：是否对服务器的ssl验证
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            // https相关：ssl主机验证方式
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1);
        }
        if ($type == 'POST') {// post请求
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        // 发出请求
        $response_content = curl_exec($curl);

        if ($response_content === false) {
            trigger_error('请求不能完成，所请求的URL为：' . $url . "\n" . 'curl错误为：' . curl_error($curl), E_USER_ERROR);
            curl_close($curl);
            return false;
        }

        curl_close($curl);
        return $response_content;
    }
}
