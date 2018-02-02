<?php

// 获取当前登录用户
if (! function_exists('auth_user')) {
    /**
     * Get the auth_user.
     *
     * @return mixed
     */
    function auth_user()
    {

        return app('Dingo\Api\Auth\Auth')->user();
    }
}

if (! function_exists('dingo_route')) {
    /**
     * 根据别名获得url.
     *
     * @param string $version
     * @param string $name
     * @param string $params
     *
     * @return string
     */
    function dingo_route($version, $name, $params = [])
    {
        return app('Dingo\Api\Routing\UrlGenerator')
            ->version($version)
            ->route($name, $params);
    }
}

if (! function_exists('trans')) {
    /**
     * Translate the given message.
     *
     * @param  string  $id
     * @param  array   $parameters
     * @param  string  $domain
     * @param  string  $locale
     * @return string
     */
    function trans($id = null, $parameters = [], $domain = 'messages', $locale = null)
    {
        if (is_null($id)) {
            return app('translator');
        }

        return app('translator')->trans($id, $parameters, $domain, $locale);
    }
}

if (! function_exists('list_to_tree')) {
    function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0)
    {
        // 创建Tree
        $tree = array();
        if (is_array($list)) {
            // 创建基于主键的数组引用
            $refer = array();
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] =& $list[$key];
            }
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId = $data[$pid];
                if ($root == $parentId) {
                    $tree[] =& $list[$key];
                } else {
                    if (isset($refer[$parentId])) {
                        $parent =& $refer[$parentId];
                        $parent[$child][] =& $list[$key];

                    }
                }
            }
        }
        return $tree;
    }
}

if (! function_exists('list_to_tree_2')) {
    function list_to_tree_2($list, $pk = 'id', $pid = 'pid', $bu_id ='menu_id' ,$child = '_child', $root = 0)
    {
        // 创建Tree
        $tree = array();
        if (is_array($list)) {
            // 创建基于主键的数组引用
            $refer = array();
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] =& $list[$key];
            }

            if(empty($refer))
                return [];
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId = $data[$pid];
                if ($root == $parentId) {
                    $tree[] =& $list[$key];
                } else {
                    foreach ($refer as $k=>$v)
                    {
                        if ($v['menu_id']==$parentId) {
                            $parent =& $refer[$parentId];
                            $parent[$child][] =& $list[$key];
                        }
                    }
                }
            }
        }
        return $tree;
    }
}

if (! function_exists('get_attr')) {
    function get_attr($a,$pid){
        $tree = array();                                //每次都声明一个新数组用来放子元素
        foreach($a as $v){
            if($v['parent_id'] == $pid){                      //匹配子记录
                $v['_child'] = get_attr($a,$v['menu_id']); //递归获取子记录
                if($v['_child'] == null){
                    unset($v['_child']);             //如果子元素为空则unset()进行删除，说明已经到该分支的最后一个元素了（可选）
                }
                $tree[] = $v;                           //将记录存入新数组
            }
        }
        return $tree;                                  //返回新数组
    }
}

function toTimeZone($src, $to_tz = 'America/Denver', $from_tz = 'Asia/Shanghai', $fm = 'Y-m-d H:i:s') {
    $datetime = new DateTime($src, new DateTimeZone($from_tz));
    $datetime->setTimezone(new DateTimeZone($to_tz));
    return $datetime->format($fm);
}

/**
 * 创建订单号
 * @param string $prefix
 * @return string
 */
function createOrderSn($prefix = "L")
{
$yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
return strtoupper($prefix).$yCode[intval(date('Y')) - 2017] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
}

function randomkeys($length)
{
    $returnStr='';
    $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
    for($i = 0; $i < $length; $i ++) {
        $returnStr .= $pattern {mt_rand ( 0, 61 )}; //生成php随机数
    }
    return str_shuffle($returnStr);
}

//菜单添加装饰前缀
function menusPrefix($menus = [],$prefix='|--')
{
    if(empty($menus))
        return false;

    foreach ($menus as $k=>$v)
    {
        if($v['parent_id'] > 0)
        {
            $menus[$k]['title_cn'] = $v['title_cn'] ? $prefix.$v['title_cn'] : "";
            $menus[$k]['title_en'] = $v['title_en'] ? $prefix.$v['title_en'] : "";
        }
    }
    return $menus;
}

//根据算法参数生成SecurityKey操
function createSecurityKey($algorithm='sha1',$str)
{
    return hash($algorithm,$str);
}

/**
 * encrypt加密
 * @param string $strtoencrypt
 * @return string
 */
function encrypt_ (string $strtoencrypt) : string
{
    $password = "kinge383e";
    for( $i=0; $i<strlen($password); $i++ ) {
        $cur_pswd_ltr = substr($password,$i,1);
        $pos_alpha_ary[] = substr(strstr(env('ALPHABET'),$cur_pswd_ltr),0,strlen(env('RALPHABET')));
    }

    $i=0;
    $n = 0;
    $nn = strlen($password);
    $c = strlen($strtoencrypt);
    $encrypted_string = '';

    while($i<$c) {
        $encrypted_string .= substr($pos_alpha_ary[$n],strpos(env('RALPHABET'),substr($strtoencrypt,$i,1)),1);
        $n++;
        if($n==$nn) $n = 0;
        $i++;
    }

    return $encrypted_string;

}

/**
 * decrypt加密
 * @param $strtodecrypt
 * @return string
 */
function decrypt_ (string $strtodecrypt): string
{
    $password = "kinge383e";
    for( $i=0; $i<strlen($password); $i++ ) {
        $cur_pswd_ltr = substr($password,$i,1);
        $pos_alpha_ary[] = substr(strstr(env('ALPHABET'),$cur_pswd_ltr),0,strlen(env('RALPHABET')));
    }

    $i=0;
    $n = 0;
    $nn = strlen($password);
    $c = strlen($strtodecrypt);
    $decrypted_string = '';

    while($i<$c) {
        $decrypted_string .= substr(env('RALPHABET'),strpos($pos_alpha_ary[$n],substr($strtodecrypt,$i,1)),1);
        $n++;
        if($n==$nn) $n = 0;
        $i++;
    }

    return $decrypted_string;

}

//记录日志操作
function addLog($info= array())
{
    $db = new \App\Models\AgentOperation;
    if(!empty($info) && !empty($info['user_name']))
    {
        $user_name = $info['user_name'];
        $user_id = $info['user_id'];
        $action_name = $info['action_name'];
    }
    $user = \Illuminate\Support\Facades\Auth::user();
    if($user)
    {
        $user_name = $user['user_name'];
        $user_id = $user['id'];
        $action_name = $info['action_name'];
    }

    if(empty($user_name) || empty($user_name) || empty($user_id) || empty($action_name))
    {
        return false;
    }
    $res = $db->insert([
        'action_name'   => $action_name,
        'user_id'       => $user_id,
        'action_user'     => $user_name,
        'action_desc'   => $info['action_desc'],
        'action_passivity'  => $info['action_passivity'],
        'action_date'   => date('Y-m-d H:i:s',time()),
        'ip_info'       => $_SERVER['SERVER_ADDR']
    ]);
}

/*
 *  把指定字段的类型由string 转换为int类型
 */
function StringShiftToInt($res,$data = []){

    if(count($data) == 0)
    {
        return $res;
    }

    foreach ($res as $k=>&$v){
        if(is_array($v)){
            foreach ($v as $key=>&$val){
                if(in_array($key,$data))
                {
                    $val = typeShift($val);
                }
            }
        }else if(is_object($v)) {
            foreach ($v as $k2=>&$v2)
            {
                if(in_array($k2,$data))
                {
                    $v2 = typeShift($v2);
                }
            }

        }else{
            if(in_array($k,$data))
            {
                $v = typeShift($v);
            }
        }
    }
    return $res;
}

/*
 *  字符串类型转换
 */
function typeShift($item)
{
    $item = (string)$item;

    //判断是否有带“.”符号，有则转换为float类型，否则转换为int类型
    if(strpos($item,".") !== false)
    {
        return (float)$item;
    }else{
        return (int)$item;
    }
}