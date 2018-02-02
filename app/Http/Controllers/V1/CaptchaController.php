<?php

namespace App\Http\Controllers\V1;

use Gregwar\Captcha\CaptchaBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class CaptchaController extends BaseController
{
    public function __construct()
    {
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Post(
     *   path="/captcha",
     *   tags={"登录"},
     *   summary="验证码",
     *   description="
     *   获取验证码
     *   成功返回字段说明
        {
        'code': 0,//状态码，0：成功，非0：错误
        'text': 'ok',//文本描述
        'result': {//结果对象
        'captcha_img': '',//验证码图片流
        'captcha_value': '6liji',//验证码值
        'gid': 'pxWlxrpOTUcEzkEHb44P'//验证码gid值
        }
        }",
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="Accept",
     *     type="string",
     *     description="http头信息",
     *     required=true,
     *     default="Accept:application/vnd.agent.v1+json"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="git",
     *     type="string",
     *     description="验证码GID",
     *     required=true,
     *     default="673mp3jjjCG9JGlV6gRf"
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function index(Request $request)
    {
        /*$tomorrow = Carbon::now()->addDay();
        $lastWeek = Carbon::now()->subWeek();
        $nextSummerOlympics = Carbon::createFromDate(2012)->addYears(4);
        $officialDate = Carbon::now()->toRfc2822String();
        $howOldAmI = Carbon::createFromDate(1975, 5, 21)->age;
        $noonTodayLondonTime = Carbon::createFromTime(12, 0, 0, 'Europe/London');
        $worldWillEnd = Carbon::createFromDate(2012, 12, 21, 'GMT');
        echo $worldWillEnd;die;*/

        //因为需要使用redis进行存储验证码字符，防止数据过大，每次请求生成验证码时都需要删除上次的验证码数据
        if($request->input('gid'))
        {
            $redis = Redis::connection("default");
            $redis->del($request->input('gid'));
        }

        $builder = new CaptchaBuilder;
        $builder->build();
        //把内容存入Redis

        $key = str_random(20);
        $redis = Redis::connection("default");
        $redis->set($key,$builder->getPhrase());
        $redis->expire($key,360);//半个小时过期

        return $this->response->array([
            'code'=>0,
            'text'=>'ok',
            'result'=>[
                'captcha_img' => $builder->inline(),
                'captcha_value' => $builder->getPhrase(),
                'gid'           => $key,
            ],
        ]);
    }

}
