<?php

namespace App\Http\Controllers\V1;

use App\Models\Agent;
use Illuminate\Http\Request;
use App\Models\Menu;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;

class AuthController extends BaseController
{

    protected $guard = 'admin';
    public function __construct()
    {
        \Auth::guard($this->guard);
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Post(
     *   path="/authorization",
     *   tags={"登录"},
     *   summary="厅主/代理商登录",
     *   description="
     *   厅主/代理商登录
     *   成功返回字段说明
    {
    'code': 0,
    'text': '认证成功',
    'result': {
    'token': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vYWdlbnQtYXBpLmRldi9hcGkvYXV0aG9yaXphdGlvbiIsImlhdCI6MTQ5MDc3MTA0NywiZXhwIjoxNDkwOTg3MDQ3LCJuYmYiOjE0OTA3NzEwNDcsImp0aSI6InBYblFwbnV3c1N6b3JhMEEiLCJzdWIiOjJ9.8OUMTZTK7sovzwduyq7c94UJjcTxOjWT9SFluk7fMko',
    'user': {
    'id': 2, //登录用户ID
    'user_name': 'agent_test',  //登录用户名
    'grade_id': 2   //登录用户类型，1为厅主，2为代理商
    },
    'menus': {},//菜单集合
    'languages': {}, //语言包集合
    'timezones': {}, //时区集合
    }
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
     *     name="password",
     *     type="string",
     *     description="密码",
     *     required=true,
     *     default="123456"
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
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function login(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'user_name' => 'required',
            'password' => 'required',
            'captcha' => 'required|max:6',
            'gid'      => 'required|max:20'
        ]);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors(),
                'result'=>'',
            ]);
        }

//        if($captcha = $request->input('captcha')){
//            if(Redis::get($request->input('gid')) != $captcha){
//                //验证码错误需要delete掉redis数据
//                Redis::del($request->input('gid'));
//                return $this->response->array([
//                    'code'=>400,
//                    'text'=>trans('auth.captcha'),
//                    'result'=>'',
//                ]);
//            }
//        }


        $where = [
            'user_name' => $request->input('user_name'),
        ];
        $info = Agent::where($where)->first();
        if( !$info) {

            return $this->response->array([
                'code'=>403,
                'text'=>trans('auth.incorrect'),
                'result'=>'',
            ]);

        }

        //测试用户不能进行登录
        if($info->account_type != 1)
        {
            return $this->response->array([
                'code'=>403,
                'text'=>trans('agent.test_account'),
                'result'=>'',
            ]);
        }

        //用户被锁定不能登录
        if($info->account_lock > 0)
        {
            return $this->response->array([
                'code'=>403,
                'text'=>trans('auth.locked'),
                'result'=>'',
            ]);
        }

        //判断用户是否被冻结
        if($info->account_state == 2)
        {
            return $this->response->array([
                'code'=>403,
                'text'=>trans('auth203.user_frost'),
                'result'=>'',
            ]);
        }

        //判断用户是否已经停用
        if($info->account_state == 3)
        {
            return $this->response->array([
                'code'=>403,
                'text'=>trans('auth203.user_disable'),
                'result'=>'',
            ]);
        }

//        //账号是否锁定
//        if($info->parent_id > 0)
//        {
//            $parentAgent = Agent::where(['id'=>$info->parent_id])->first();
//            if($parentAgent->lock_rank == 1)
//            {
//
//            }
//        }

        if( ! $info['salt'] ) {

            Agent::where($where)->update(['salt' =>randomkeys(20) ]);
        }
        $salt = Agent::where($where)->pluck('salt')[0];
        $credentials = $request->only('user_name', 'password');
        $credentials['password'] .= $salt;
        $credentials['account_state'] = 1;
        // 验证失败返回403
        if (! $token = Auth::attempt($credentials)) {
            return $this->response->array([
                'code'=>403,
                'text'=>trans('auth.incorrect'),
                'result'=>'',
            ]);
        }

        //用户信息
        $user = Agent::select('id','user_name','grade_id','is_hall_sub','group_id')->where(['user_name'=>$credentials['user_name'],'account_state'=>1])->first();


            $roles = [];
            foreach ($user->roles as $role) {
                $roles[] = $role->id;
                $roles[] = $role->parent_id;
            }
            unset($user->roles);
            $roles = array_unique($roles);

       //获取用户菜单权限菜单栏
        $sysMenus = DB::table('agent_system_menus')->orderBy('sort_id')->where('state',1)->get()->toArray();
        $agentMenusList = DB::table('agent_menus_list')->orderBy('sort_id')->where('state',1)->where('grade_id',$user->grade_id)->get()->toArray();
        $menus = [];
        if($user->is_hall_sub == 1)
        {//如果是子账号则根据其所属分组进行权限获取
            $subMenus = DB::table('agent_role_group_menus')->where(['role_id'=>$user->group_id])->get()->toArray();
        }
        else
        {//厅主和代理权限则是系统级别
//            $subMenus = DB::table('agent_menus_list')->where(['user_id'=>$user->id])->get()->toArray();
            $subMenus = $agentMenusList;
        }


        foreach ($agentMenusList as $k=>$v)
        {
            foreach ($subMenus as $kk=>$vv)
            {
                if($v->menu_id == $vv->menu_id)
                {
                    $menus[] = [
                        'id'    => $v->id,
                        'menu_id'   => $v->menu_id,
                        'parent_id' => $v->parent_id,
                        'title_cn'  => $v->title_cn,
                        'title_en'  => $v->title_en,
                        'class' => $v->class,
                        'desc'  => $v->desc,
                        'link_url'   =>$v->link_url,
                        'icon'  => $v->icon,
                        'state' => $v->state,
                        'sort_id'   => $v->sort_id,
                        'menu_code' => $v->menu_code,
                    ];
                }
            }
        }

//        if($user->is_hall_sub == 1) //子账户获取系统组权限信息比对
//        {
//            $groupMenus = DB::table('agent_role_group_menus')->where(['role_id'=>$user->group_id])->get()->toArray();
//            $groupMenusMenusIdList = array_column($groupMenus,'menu_id');
//            foreach ($menus as $kk=>$gv)
//            {
//                if(!in_array($gv['menu_id'],$groupMenusMenusIdList))
//                {
//                    unset($menus[$kk]);
//                }
//            }
//        }

        $parentMenus = [];
        $parentids = array_unique(array_column($menus,'parent_id'));
        $nowMenuids = array_unique(array_column($menus,'menu_id'));

        foreach ($sysMenus as $k=>$v)
        {
            if(in_array($v->id,$parentids) && !in_array($v->id,$nowMenuids))
            {
                $parentMenus[] = [
                    'id'    => $v->id,
                    'menu_id'   => $v->id,
                    'parent_id' => $v->parent_id,
                    'title_cn'  => $v->title_cn,
                    'title_en'  => $v->title_en,
                    'class' => $v->class,
                    'desc'  => $v->desc,
                    'link_url'   =>$v->link_url,
                    'icon'  => $v->icon,
                    'state' => $v->state,
                    'sort_id'   => $v->sort_id,
                    'menu_code' => $v->menu_code,
                ];
            }
        }

            $menus = array_merge($parentMenus,$menus);
            //权限菜单TODO
            $menus = get_attr($menus,'0');
        //登陆成功更新登陆IP地址
        Agent::where(['id'=>$user->id])->update(['ip_info'=>$request->ip(),'update_time'=>date('Y-m-d H:i:s',time())]);

        // 添加操作日志
        @addLog([
            'action_name'=>'登陆操作',
            'action_desc'=>"{$user->user_name}登陆操作",
            'action_passivity'=>'代理商账号表'
        ]);

        //语言列表
//        $language = language::get()->toArray();
        
        // 添加操作日志
        @addLog([
            'action_name'=>'登陆操作',
            'action_desc'=>"{$user->user_name}登陆操作",
            'action_passivity'=>$user->user_name
        ]);

        //用户权限
        return $this->response->array([
            'code' => 0,
            'text' => trans('auth.success'),
            'result' => [
                'token' => $token,
                'user' => $user,
                'menus' => $menus,
                'languages' =>config('language'),
                'timezones' =>config('timezones'),
            ]
        ]);
    }

    /**
     * @api {post} /auth/token/new 刷新token(refresh token)
     * @apiDescription 刷新token(refresh token)
     * @apiGroup Auth
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Authorization 用户旧的jwt-token, value已Bearer开头
     * @apiHeaderExample {json} Header-Example:
     *     {
     *       "Authorization": "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjEsImlzcyI6Imh0dHA6XC9cL21vYmlsZS5kZWZhcmEuY29tXC9hdXRoXC90b2tlbiIsImlhdCI6IjE0NDU0MjY0MTAiLCJleHAiOiIxNDQ1NjQyNDIxIiwibmJmIjoiMTQ0NTQyNjQyMSIsImp0aSI6Ijk3OTRjMTljYTk1NTdkNDQyYzBiMzk0ZjI2N2QzMTMxIn0.9UPMTxo3_PudxTWldsf4ag0PHq1rK8yO9e5vqdwRZLY"
     *     }
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *      {
     *       "code":0,
     *       "text":"刷新成功",
     *       "result":"9UPMTxo3_PudxTWldsf4ag0PHq1rK8yO9e5vqdwRZLY.eyJzdWIiOjEsImlzcyI6Imh0dHA6XC9cL21vYmlsZS5kZWZhcmEuY29tXC9hdXRoXC90b2tlbiIsImlhdCI6IjE0NDU0MjY0MTAiLCJleHAiOiIxNDQ1NjQyNDIxIiwibmJmIjoiMTQ0NTQyNjQyMSIsImp0aSI6Ijk3OTRjMTljYTk1NTdkNDQyYzBiMzk0ZjI2N2QzMTMxIn0.eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9"
     *     }
     */
    public function refreshToken()
    {

        $token = \Auth::refresh();
//        return $this->response->array(compact('token'));
        return $this->response->array([
            'code'=>0,
            'text'=>trans('auth.refresh'),
            'result'=>compact('token'),
        ]);

    }

    /**
     * @api {post} /users 注册(register)
     * @apiDescription 注册(register)
     * @apiGroup Auth
     * @apiPermission none
     * @apiVersion 0.1.0
     * @apiParam {Email}  email   email[unique]
     * @apiParam {String} password   password
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         token: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjEsImlzcyI6Imh0dHA6XC9cL21vYmlsZS5kZWZhcmEuY29tXC9hdXRoXC90b2tlbiIsImlhdCI6IjE0NDU0MjY0MTAiLCJleHAiOiIxNDQ1NjQyNDIxIiwibmJmIjoiMTQ0NTQyNjQyMSIsImp0aSI6Ijk3OTRjMTljYTk1NTdkNDQyYzBiMzk0ZjI2N2QzMTMxIn0.9UPMTxo3_PudxTWldsf4ag0PHq1rK8yO9e5vqdwRZLY
     *     }
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *         "email": [
     *             "该邮箱已被他人注册"
     *         ],
     *     }
     */
    public function register(Request $request)
    {


        $validator = \Validator::make($request->input(), [
            'user_name' => 'required|unique:lb_platform_user',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->response->array([
                'code' => 400,
                'text'=> $validator->messages(),
                'result' => '',
            ]);
        }

        $name = $request->get('user_name');
        $password = $request->get('password');

        $attributes = [
            'user_name' => $name,
            'password' => app('hash')->make($password.randomkeys(20)),
        ];
        $user = PlatformUser::create($attributes);

        // 用户注册事件

        $token = \Auth::fromUser($user);

        // 用户注册成功后发送邮件
        // 或者 \Queue::push(new SendRegisterEmail($user));
        //dispatch(new SendRegisterEmail($user));

        return $this->response->array([
            'code' => 0,
            'text'=> trans('auth.register'),
            'result' => compact('token'),
        ]);
    }

    /**
     * @api {post} /language 语言列表
     * @apiDescription 语言列表
     * @apiGroup Auth
     * @apiPermission none
     * @apiVersion 0.1.0
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *      {
                "code": 0,
                "text": "操作成功",
                "result": {
                    "language": {
                        "简体中文": "zh-cn",
                        "English": "en",
                        "繁体中文": "zh-tw"
                    }
                }
            }
     */
    public function language()
    {
        return $this->response->array([
            'code' => 0,
            'text'=> trans('agent.success'),
            'result' => [
                'language' => config('language'),
            ],
        ]);
    }
}
