<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkj.com
 * Date: 2017/3/29
 * Time: 15:40
 * 密码相关控制器
 */

namespace App\Http\Controllers\V1;


use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;

class PasswordController extends BaseController
{
    /**
     * consumes={"multipart/form-data"},
     * @SWG\Post(
     *   path="/auth/getPwd",
     *   tags={"登录"},
     *   summary="厅主/代理商找回密码",
     *   description="
     *   厅主/代理商登录
     *   成功返回字段说明
    {
    'code': 0, //返回成功状态证明邮件已经发送成功
    'text': 'Success',
    'result': {
    }
    ",
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
     *     name="user_name",
     *     type="string",
     *     description="用户名",
     *     required=true,
     *     default="agent_test"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="email",
     *     type="string",
     *     description="邮箱",
     *     required=true,
     *     default="123456@qq.com"
     *   ),
     *  @SWG\Parameter(
     *     in="formData",
     *     name="captcha",
     *     type="string",
     *     description="验证码",
     *     required=true,
     *     default="12345"
     *   ),
     *  @SWG\Parameter(
     *     in="formData",
     *     name="gid",
     *     type="string",
     *     description="验证码GID",
     *     required=true,
     *     default="673mp3jjjCG9JGlV6gRf"
     *   ),
     *  @SWG\Parameter(
     *     in="formData",
     *     name="token",
     *     type="string",
     *     description="token",
     *     required=true,
     *     default="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vYWdlbnQtYXBpLmRldi9hcGkvYXV0aG9yaXphdGlvbiIsImlhdCI6MTQ5MDc3MTA0NywiZXhwIjoxNDkwOTg3MDQ3LCJuYmYiOjE0OTA3NzEwNDcsImp0aSI6InBYblFwbnV3c1N6b3JhMEEiLCJzdWIiOjJ9.8OUMTZTK7sovzwduyq7c94UJjcTxOjWT9SFluk7fMko"
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function retrievePwd(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'user_name' => 'required',
            'email' => 'required|email',
            'captcha' => 'required|max:6',
            'gid'      => 'required|max:20'
        ]);
        //数据验证不通过
        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors(),
                'result'=>'',
            ]);
        }

        //查看对应账号和邮箱是否匹配
        $find = Agent::where(['email'=>$request->input('email'),'user_name'=>$request->input('user_name')])->first();
        if(!$find)
        {
            return $this->response->array([
                'code'=>400,
                'text'=>trans('pwd.email_user_name'),
                'result'=>'',
            ]);
        }
        //数据验证通过进行生成找回密码验证码
        $credentials = ['email'=>$request->input('email'),'user_name'=>$request->input('user_name'),'get_time'=>time()];
        //生成验证码操作
        $code = strtoupper(str_shuffle(str_random(5)));
        //存放到redis中
        $key = env('GET_PWD_PIX').$code;
        $values = json_encode($credentials);
        $expire = (int)env('GET_PWD_UNIT') * 3600;
        $redis = Redis::connection('default');
        $redis->set($key,$values);
        $redis->expire($key,$expire);

        //进行邮件发送
        $this->sendEmail($code,$request->input('email'),$request->input('user_name'));

        // 添加操作日志
        @addLog([
            'action_name'=>'找回密码操作',
            'action_desc'=>"{$find->user_name}修改密码",
            'action_passivity'=>'代理商账号表'
        ]);

        return $this->response->array([
            'code'=>0,
            'text'=>trans('pwd.email_send_success'),
            'result'=>'',
        ]);

    }

    //邮箱验证码发送操作
    private function sendEmail($code = null,$to = null,$user_name = null)
    {
//        $emailContent = "您的验证码为：".$code;
//        $emailContent = $this->getEmailHtml($code);
//        $subject = "找回密码";
        $data = ['email'=>$to, 'code'=>$code,'user_name'=>$user_name];
        $send = Mail::send('pwd_email',$data,function($massage) use ($data){
            return $massage->to($data['email'])->subject('找回密码');
        });

        return true;
    }

    //获取邮件HTML模板
    private function getEmailHtml($code)
    {
        return "<!DOCTYPE html><html lang=\"en\"><head><meta charset=\"UTF-8\"><style>body{font:14px \"microsoft yahei\",Arial,sans-serif;margin:0;padding:0;color:#35ADDC}article,aside,dialog,footer,header,section,footer,nav,figure,menu{display:block}ul,p,h1,h2,h3,h4,h5,h6,dl,dd{margin:0;padding:0;list-style:none}table{border-collapse:collapse;border-spacing:0}html,body{height:100%;width:100%;overflow:hidden}*{box-sizing:border-box}.container{width:100%;height:100%}.email-main{width:512px;height:507px}.email-head{width:100%;height:82px;background-color:#191e27;border-bottom:1px solid #5B6271}.email-body{padding-top:100px;padding-left:45px;width:100%;height:430px;background-color:#242E42}.head-text{font-size:36px;color:#35ADDC;line-height:82px;margin-left:47px}.msg{color:#fff}.top{margin-top:20px}.top2{margin-top:40px}.top3{margin-top:150px}</style></head><body><div class=\"container\"><div class=\"email-main\"><div class=\"email-head\"><b class=\"head-text\">LEBO</b></div><div class=\"email-body\"><div class=\"email-content\"><div class=\"msg\"><div><b style=\"font-size:18px\">尊敬的的xxx:</b></div><div class=\"top\"><span>以下是您登陆账户xxx是所需的验证码:</span></div><div class=\"top2\"><b style=\"font-size:20px;color:#35ADDC\">$code</b></div></div><div class=\"top3\"><b style=\"font-size:20px\">LEBO团队</b></div></div></div></div></div></body></html>";
    }


    /**
     * consumes={"multipart/form-data"},
     * @SWG\Patch(
     *   path="/auth/emailPwd",
     *   tags={"登录"},
     *   summary="厅主/代理商邮件验证码，修改密码",
     *   description="
     *   厅主/代理商邮件验证码，修改密码
     *   成功返回字段说明
    {
    'code': 0, //返回成功状态证明密码修改成功
    'text': '操作成功',
    'result': {
    }
    ",
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
     *     name="password",
     *     type="string",
     *     description="新密码",
     *     required=true,
     *     default="123456"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="password_confirmation",
     *     type="string",
     *     description="密码确认",
     *     required=true,
     *     default="123456"
     *   ),
     *  @SWG\Parameter(
     *     in="formData",
     *     name="captcha",
     *     type="string",
     *     description="验证码",
     *     required=true,
     *     default="3FKSX"
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function emailEditPwd(Request $request)
    {
        $code = $request->input('captcha');
        $password = $request->input('password');

        //进行提交数据验证
        $message = [
            'password.required'     => trans('role.password.required'),
            'password.max'          => trans('role.password.max'),
            'password.min'          => trans('role.password.min'),
            'password.confirmation' => trans('role.password.confirmation'),
            'captcha.required'         => trans('pwd.pwd_captcha'),
        ];
        $validator = \Validator::make($request->input(),[
            'captcha' => 'required',
            'password'  => 'required|confirmed|max:20|min:6',
        ],$message);

        //数据验证不通过
        if($validator->fails())
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => $validator->errors()->first(),
                'result'    => ''
            ]);
        }

        //验证code是否正确
        $rediskey = env('GET_PWD_PIX').strtoupper($code);
        $redisResult = json_decode(Redis::get($rediskey),true);
        if(!$redisResult)
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => trans('pwd.code_error'),
                'result'    => ''
            ]);
        }

        //进行密码修改操作

        $findSub = Agent::where(['user_name'=>$redisResult['user_name'],'email'=>$redisResult['email']])
            ->first();
        $attributes['password'] = app('hash')->make($password.$findSub->salt);
        $attributes['update_time'] = date('Y-m-d H:i:s',time());
        $res = $findSub->update($attributes);
        if(!$res)
        {
            return $this->response()->array([
                'code'  => 400,
                'text'      => trans('role.fails'),
                'result'    => ''
            ]);
        }



        // 添加操作日志
        @addLog([
             'action_name'=>'邮箱修改密码操作',
             'action_desc'=>"{$findSub->user_name}修改密码",
             'action_passivity'=>'代理商账号表'
        ]);

        return $this->response->array([
            'code'      => 0,
            'text'      => trans('role.success'),
            'result'    => ''
        ]);
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Patch(
     *   path="/auth/editPwd/{id}",
     *   tags={"代理管理"},
     *   summary="厅主/代理商修改密码",
     *   description="
     *   厅主/代理商修改密码
     *   PS:该接口为公用型接口，代理商和厅主修改密码操作都调用该接口
     *   成功返回字段说明
    {
    'code': 0, //返回成功状态证明密码修改成功
    'text': '操作成功',
    'result': {
    }
    ",
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
     *     name="password",
     *     type="string",
     *     description="新密码",
     *     required=true,
     *     default="123456"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="password_confirmation",
     *     type="string",
     *     description="密码确认",
     *     required=true,
     *     default="123456"
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function editPwd(Request $request,$id)
    {
        //进行提交数据验证
        $message = [
            'password.required'     => trans('role.password.required'),
            'password.max'          => trans('role.password.max'),
            'password.min'          => trans('role.password.min'),
            'password.confirmation' => trans('role.password.confirmation'),
        ];
        $validator = \Validator::make($request->input(),[
            'password'  => 'required|confirmed|max:20|min:6',
        ],$message);

        //数据验证不通过
        if($validator->fails())
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => $validator->errors()->first(),
                'result'    => ''
            ]);
        }
        //判断ID是否合法
        $find = Agent::where(['id'=>$id])->first();
        if(!$find)
        {
            return $this->response->array([
                'code'      => 400,
                'text'      => trans('pwd.data_error'),
                'result'    => ''
            ]);
        }

        //数据验证通过进行密码修改操作
        $attributes = $request->except('token','locale','password_confirmation','s','menu_id');//过滤掉token 和 locale字段
        $attributes['password'] = app('hash')->make($request->input('password').$find->salt);
        $attributes['update_time'] = date('Y-m-d H:i:s',time());
        $res = Agent::where('id',$id)->update($attributes);
        if(!$res)
        {
            return $this->response->array([
                'code'      => 400,
                'text'      => trans('role.fails'),
                'result'    => ''
            ]);
        }


        // 添加操作日志
        @addLog([
            'action_name'=>'修改密码操作',
            'action_desc'=>"{$find->user_name}修改密码",
            'action_passivity'=>$find->user_name
        ]);



        //密码修改成功
        return $this->response->array([
            'code'      => 0,
            'text'      => trans('role.success'),
            'result'    => ''
        ]);

    }
}