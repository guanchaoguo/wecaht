<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkj.com
 * Date: 2017/3/30
 * Time: 13:07
 * 厅主查看代理商管理
 */

namespace App\Http\Controllers\V1;


use App\Models\Agent;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AgentMenuList;
use App\Models\AgentMenu;
use App\Models\AgentMenus;
use App\Models\Whitelist;
use Illuminate\Support\Facades\Redis;


class AgentController extends BaseController
{

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Post(
     *   path="/agent",
     *   tags={"代理管理"},
     *   summary="厅主查看代理商列表",
     *   description="
     *   厅主查看代理商列表
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': {
        'total': 1,//总的记录条数
        'per_page': 15,//每页条数
        'current_page': 1,//当前页
        'last_page': 1,//最后一页
        'next_page_url': null,//下一页地址
        'prev_page_url': null,//上一页地址
        'from': 1,
        'to': 1,
        'data': [
                {
                'id': 62,   //代理商ID
                'user_name': '12345454',//代理商登录名
                'real_name': '',//代理商用户名
                'tel': '',//代理商电话
                'sub_count': 0,//有几个下级代理,代理数
                'sub_user': 0,//玩家数
                'add_time': '2017-03-28 13:42:30',//添加时间
                'account_lock': 0,//是否锁定,1为永久锁定,0为未锁定,7为锁定7天,30为锁定30天
                'parent_id': 2  //所属代理商ID
                }
            ]
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
     *     name="locale",
     *     type="string",
     *     description="语言",
     *     required=true,
     *     default="cn"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="user_name",
     *     type="string",
     *     description="用户名",
     *     required=true,
     *     default="123456"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="tel",
     *     type="string",
     *     description="手机号码",
     *     required=true,
     *     default="123456"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="user_name",
     *     type="string",
     *     description="用户名",
     *     required=true,
     *     default="123456"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="account_lock",
     *     type="string",
     *     description="是否锁定,1为永久锁定,0为未锁定,7为锁定7天,30为锁定30天",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="start_add_time",
     *     type="string",
     *     description="开始时间",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="account_lock",
     *     type="string",
     *     description="是否锁定,1为永久锁定,0为未锁定",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="page",
     *     type="string",
     *     description="当前页",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="page_num",
     *     type="string",
     *     description="每页条数",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="is_page",
     *     type="number",
     *     description="是否分页,是否分页 1：是，0：否 ，默认1",
     *     required=true,
     *     default="1"
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
    public function agentList(Request $request)
    {
        $user_name = $request->input('user_name');
        $tel = $request->input('tel');
        $account_lock = $request->input('account_lock');
        $page_num = $request->input('page_num',10);
        $is_page = $request->input('is_page',1);

        $start_add_time = $request->input('start_add_time');
        $end_add_time = $request->input('end_add_time');

        $db = Agent::select('id','user_name','real_name',/*'tel',*/'sub_count','sub_user','add_time','account_lock','parent_id','account_type');

        if(isset($user_name) && !empty($user_name)) {
            $db->where('user_name', 'like', '%'.$user_name.'%');
        }

        if(isset($tel) && !empty($tel)) {
           // $db->where('tel','=',$tel);
        }

        if(isset($account_lock) && $account_lock !== '') {
            $db->where('account_lock','=',$account_lock);
        }

        if(isset($start_add_time) && !empty($start_add_time)) {
            $db->where('add_time', '>=', $start_add_time);
        }

        if(isset($end_add_time) && !empty($end_add_time)) {
            $db->where('add_time', '<', $end_add_time);
        }

        $db->where('parent_id','=',$this->agentId);
        $db->where('grade_id','=',2);

        $db->orderby('id', 'desc');
        if($is_page) {
            $agents = $db->paginate($page_num)->toArray();
        } else {
            $agents = [
                'data' =>$db->get()->toArray()
            ];
        }

        //数据为空时
        if(!$agents)
        {
            return $this->response->array([
                'code' => 0,
                'text' =>trans('role.empty_list'),
                'result' => $agents,
            ]);
        }

        foreach ($agents['data'] as &$v){
            $v['sub_count'] = (int) $v['sub_count'];
            $v['sub_user'] = (int) $v['sub_user'];
        }

        unset($v);
        //正常返回数据
        return $this->response->array([
            'code' => 0,
            'text' =>trans('agent.success'),
            'result' => $agents,
        ]);
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Post(
     *   path="/agent/store",
     *   tags={"代理管理"},
     *   summary="厅主添加代理",
     *   description="
     *   厅主添加代理
     *   成功返回字段说明
    {
    'code': 0,
    'text': '保存成功',
    'result': ''
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
     *     name="locale",
     *     type="string",
     *     description="语言",
     *     required=true,
     *     default="cn"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="account_type",
     *     type="number",
     *     description="账号种类,1为正常账号,2为测试账号（添加测试账号的时候传2）",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="area",
     *     type="string",
     *     description="运营地区",
     *     required=true,
     *     default="123456"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="tel_pre",
     *     type="string",
     *     description="手机国家代码",
     *     required=true,
     *     default="123456"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="tel",
     *     type="string",
     *     description="手机号",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="user_name",
     *     type="string",
     *     description="登录名",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="real_name",
     *     type="string",
     *     description="用户名",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="password",
     *     type="string",
     *     description="密码",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="password_confirmation",
     *     type="string",
     *     description="确认密码",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="email",
     *     type="string",
     *     description="邮箱",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="account_lock",
     *     type="string",
     *     description="是否锁定",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="lock_reason",
     *     type="string",
     *     description="锁定原因",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="time_zone",
     *     type="string",
     *     description="时区",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="agent_code",
     *     type="string",
     *     description="Agent Code",
     *     required=true,
     *     default="hq"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="menus",
     *     type="string",
     *     description="菜单权限数组格式['2-1','2-1']",
     *     required=true,
     *     default="hq"
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
    public function addAgent(Request $request)
    {
        $message = [
            'area.required' => trans('agent.area.required'),
            'time_zone.required' => trans('agent.time_zone.required'),
            'user_name.required' => trans('agent.agent_name.required'),
            'user_name.unique' => trans('agent.agent_name.unique'),
            'user_name.regex' => trans('agent.agent_name.regex'),
            'real_name.required' => trans('agent.real_name.required'),
            'real_name.regex' => trans('agent.real_name.regex'),
            'password.required' => trans('agent.password.required'),
            'password.min' => trans('agent.password.min'),
            'password.confirmed' => trans('agent.password.confirmed'),
            'email.required' => trans('agent.email.required'),
            'email.email' => trans('agent.email.email'),
            'email.unique' => trans('agent.email.unique'),
            'tel.required' => trans('agent.tel.required'),
        ];
        $validator = \Validator::make($request->input(), [
            'user_name' => [
                'required',
                'unique:lb_agent_user',
                'regex:/^[a-zA-z][a-zA-Z0-9_]{5,19}$/'
            ],
            'real_name' => [
                'required',
//                'regex:/^[\w\_\x{4e00}-\x{9fa5}]{6,20}$/u'//中文、英文、数字、下划线结合而且6-20字符
            ],
            'password' => 'required|min:6|confirmed',
            'tel' => 'required',
            'email' => 'required|email|unique:lb_agent_user',
            'area' => 'required',
            'time_zone' => 'required',
        ],$message);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        //代理商code
        $agent_code =  $request->input('agent_code');//添加代理时必须
        if( ! $agent_code ) {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.agent_code.required'),
                'result'=>'',
            ]);
        }

        if( ! preg_match('/^[a-zA-z][a-zA-Z0-9_]{2,5}$/', $agent_code) ) {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.agent_code.error'),
                'result'=>'',
            ]);
        }
        if( Agent::where(['agent_code' => $agent_code])->first() ) {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.agent_code.unique'),
                'result'=>'',
            ]);
        }

        //数据验证通过进行添加操作
        $attributes = $request->except('token','password_confirmation','games','agent_id','locale','s','menus','menu_id');

        if($attributes['tel']) {
            $attributes['tel'] = (string) $attributes['tel'];
        }
        $attributes['agent_code'] = $agent_code;

        $attributes['ip_info'] = $request->ip();
        $attributes['salt'] = randomkeys(20);

        $attributes['password'] = app('hash')->make($attributes['password'].$attributes['salt']);
        $attributes['grade_id'] = 2;
        $attributes['parent_id'] = $this->agentId;
        $attributes['add_time'] = date("Y-m-d H:i:s",time());
        $attributes['update_time'] = date("Y-m-d H:i:s",time());

        DB::beginTransaction();

        $user = Agent::create($attributes);

        if(!$user)
        {
            DB::rollBack();
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.add_fails'),
                'result' => '',
            ]);
        }

        //更改厅主代理商数
        if($user->parent_id) {
            Agent::where('id',$user->parent_id)->increment('sub_count');
        }

        if($request->input('menus')) {
            //开通菜单权限
            $openMenuRole = self::openMenuRole($request->input('menus'), $user->id);
            if($openMenuRole['code'] != 1) {
                DB::rollBack();
                return $this->response->array($openMenuRole['data']);
            }
        }

        // 添加操作日志
         $user = \Illuminate\Support\Facades\Auth::user();
         @addLog([
             'action_name'=>'添加代理',
             'action_desc'=>"厅主{$user['user_name']}添加代理商{$request->input('user_name')}",
             'action_passivity'=>$request->input('user_name')
         ]);
        DB::commit();
        //操作成功返回
        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.save_success'),
            'result' => '',
        ]);

    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Post(
     *   path="/agent/{agent_id}/menus",
     *   tags={"代理管理"},
     *   summary="保存代理商菜单权限",
     *   description="
     *   保存代理商菜单权限
     *   成功返回字段说明
    {
    'code': 0,
    'text': '保存成功',
    'result': ''
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
     *     name="locale",
     *     type="string",
     *     description="语言",
     *     required=true,
     *     default="cn"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="menus",
     *     type="string",
     *     description="菜单数组 格式：['2-1','33-2']['id-parent_id']",
     *     required=true,
     *     default="1"
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
    public function setMenuRole(Request $request, int $agent_id)
    {
        if($this->agentInfo['grade_id'] == 2) {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.grade_id_error'),
                'result' => '',
            ]);
        }
        $agent = Agent::where(['id'=>$agent_id,'is_hall_sub' =>0,'grade_id' => 2])->first();
        //代理商验证
        if( ! $agent ) {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.agent_not_exist'),
                'result'=>'',
            ]);
        }
        $data = $request->input('menus');
        $re = self::openMenuRole($data,$agent_id);
        if($re['code']) {

            $agent_name = '代理商';
            @addLog([
                'action_name'=>'修改'.$agent_name.'的权限',
                'action_desc'=>$this->agentInfo['user_name'].' 对 '.$agent_name.$agent['user_name'].'的权限进行修改',
                'action_passivity'=> $agent['user_name']
            ]);

            return $this->response->array([
                'code'=>0,
                'text'=> trans('agent.success'),
                'result'=>'',
            ]);
        } else {
            return $this->response->array($re['data']);
        }

    }

    /**
     * @param array $data 菜单数据格式 ['2-1','33-2']，[id-parent_id]
     * @param int $agent_id 厅主id
     * @return array
     */
    protected function openMenuRole(array $data , int $agent_id)
    {
        if( $data ) {
            $data_arr = [];
            foreach ($data as $menu) {

                $game_tmp = explode('-',$menu);
                $data_arr[] = [
                    'user_id'=> $agent_id,
                    'menu_id' => $game_tmp[0],
                    'parent_id' => $game_tmp[1],
                ];

            }

            $re = AgentMenus::where('user_id',$agent_id)->get();
            $re && AgentMenus::where('user_id',$agent_id)->delete();
            $res_game = AgentMenus::insert($data_arr);
            if ($res_game) {
                return [
                    'code' => 1
                ];
            }

            return [
                'code' => 0,
                'data' => [
                    'code' => 400,
                    'text' => trans('agent.save_fails'),
                    'result' => '',
                ]
            ];
        }
        return [
            'code' => 1
        ];
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Post(
     *   path="/agent/{id}",
     *   tags={"代理管理"},
     *   summary="查看厅主/代理商基本信息接口",
     *   description="
     *   查看厅主/代理商信息接口；
     *   PS: 该接口为公用性接口；厅主编辑代理商时获取数据、厅主查看代理商信息都是用该接口
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': {
    'agent': {
    'id': 80,	//厅主、代理商ID
    'user_name': 'agent001',	//厅主、代理商登录名称
    'salt': 'Rd8cVH8gBXesRG1CS6Rs',
    'real_name': '厅主添加代理商测试1号',	//厅主、代理商用户名
    'desc': null,	//厅主、代理商描述说明
    'grade_id': 2,	//厅主和代理商的区别字段，1为厅主，2为代理商
    'tel': '13641459225',	//厅主、代理商的电话
    'account_state': 1, //状态，1为正常,2为停用,3为删除
    'add_time': '2017-03-31 13:04:47',	//添加时间
    'update_time': '2017-03-31 13:04:47', //修改时间
    'ip_info': '192.168.29.62', //注册IP地址
    'parent_id': 2,	//所属厅主ID
    'parent_agent': test // 直属厅主
    'mapping': null,
    'sub_count': 0,	//	有几个下级代理,代理数
    'area': 'shanghai',	//运营地区
    'tel_pre': '86',	//手机国家代码
    'email': '687947865@qq.com',	//邮箱
    'account_lock': 0,	//是否锁定,1为永久锁定,0为未锁定,7为锁定7天,30为锁定30天
    'lock_rank': 0,
    'charge_mode': null,	//1为固定收费,2为分享报表流水
    'charge_fixed': null, //固定收费百分比,入库记录为整数
    'charge_percentage': null,
    'time_zone': 'BeijingAsia/Hong_Kong', //时区
    'lang_code': null,
    'sub_user': 0, //玩家数
    'lock_reason': '', //锁定原因
    'account_type': 1, //账号种类,1为正常账号,2为测试账号
    'agent_code': ''
    }
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
     *     name="grade_id",
     *     type="string",
     *     description="类型ID,如果是厅主查看代理商或者代理商查看信息，则值为2，厅主查看自己基本信息为1，默认为1",
     *     required=true,
     *     default="agent_test"
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
    public function getAgent(Request $request, $id)
    {
        $validator = \Validator::make($request->all(), [
            'grade_id' => 'required|Integer',
        ]);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.grade_id_error'),
                'result'=>'',
            ]);
        }
        $grade_id = $request->input('grade_id',1);
        $data = Agent::where(['grade_id'=>$grade_id, 'id'=>$id])->first();
        if(!$data)
        {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.agent_not_exist'),
                'result'=>'',
            ]);
        }
        $data->account_lock = $data->account_lock;
        if($data->parent_id > 0)
        {
            $parent = Agent::where(['id'=>$data->parent_id])->first();
            $data->parent_agent = $parent->user_name;
        }

        //正常返回数据
        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>[
                'agent' => $data,
            ],
        ]);
    }


    /**
     * consumes={"multipart/form-data"},
     * @SWG\Patch(
     *   path="/agent/{id}",
     *   tags={"代理管理"},
     *   summary="厅主修改保存代理",
     *   description="
     *   厅主修改保存代理
     *   成功返回字段说明
    {
    'code': 0,
    'text': '保存成功',
    'result': ''
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
     *     name="locale",
     *     type="string",
     *     description="语言",
     *     required=true,
     *     default="cn"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="account_type",
     *     type="number",
     *     description="账号种类,1为正常账号,2为测试账号（添加测试账号的时候传2）",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="area",
     *     type="string",
     *     description="运营地区",
     *     required=true,
     *     default="123456"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="tel_pre",
     *     type="string",
     *     description="手机国家代码",
     *     required=true,
     *     default="123456"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="tel",
     *     type="string",
     *     description="手机号",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="user_name",
     *     type="string",
     *     description="登录名",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="real_name",
     *     type="string",
     *     description="用户名",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="password",
     *     type="string",
     *     description="密码",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="password_confirmation",
     *     type="string",
     *     description="确认密码",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="email",
     *     type="string",
     *     description="邮箱",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="account_lock",
     *     type="string",
     *     description="是否锁定",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="lock_reason",
     *     type="string",
     *     description="锁定原因",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="time_zone",
     *     type="string",
     *     description="时区",
     *     required=true,
     *     default="1"
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
    public function update(Request $request,$id)
    {
        $message = [
            'area.required' => trans('agent.area.required'),
            'tel.required' => trans('agent.tel.required'),
            'time_zone.required' => trans('agent.time_zone.required'),
            'email.required' => trans('agent.email.required'),
            'email.email' => trans('agent.email.email'),
            'email.unique' => trans('agent.email.unique'),
        ];
        $validator = \Validator::make($request->input(), [
//            'tel' => 'required',
//            'email' => 'required|email|unique:lb_agent_user,email,'.$id,
            'area' => 'required',
            'time_zone' => 'required',

        ],$message);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        //数据验证通过进行添加操作
        $agent_id = $request->input('agent_id');
        $attributes = $request->except('token','password_confirmation','games','agent_id','locale','s','parent_agent');

        if($attributes['tel']) {
            $attributes['tel'] = (string) $attributes['tel'];
        }

        $attributes['ip_info'] = $request->ip();
//        $attributes['salt'] = randomkeys(20);
//
//        $attributes['password'] = app('hash')->make($attributes['password'].$attributes['salt']);
        $attributes['update_time'] = date("Y-m-d H:i:s",time());
        if($agent_id == 1 || !isset($attributes['account_lock']))
        {
            $user = Agent::where(['id'=>$id])->update($attributes);//锁定账号
        }
        else if(in_array($attributes['account_lock'],[0,1]))
        {
            //锁定代理商后锁定账号和旗下玩家
            DB::beginTransaction();
            try{
                $user = Agent::where(['id'=>$id])->update($attributes);//锁定账号
                if(!$user)
                {
                    throw new \Exception("update error");
                }
                //锁定旗下玩家操作
                $accountState = $attributes['account_lock'] > 0 ? 3 : 1;
                $subAccount = $this->lockUser($accountState, $id, 2);
                if($subAccount === false)
                {
                    throw new \Exception("account error");
                }
                DB::commit();//事物提交
            }catch (\Exception $e)
            {
                DB::rollBack();//事物回滚

                return $this->response()->array([
                    'code'  => 400,
                    'text'  => trans('agent.save_fails'),
                    'result'   => ''
                ]);
            }
        }


        if(!isset($user))
        {
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.save_fails'),
                'result' => '',
            ]);
        }

        // 添加操作日志
        $accounType =  $request->input('account_type') == 1 ? '正常账号':'测试账号';  // 1为正常账号,2为测试账号（添加测试账号的时候传2）
        @addLog([
            'action_name'=>'修改代理账号类型',
            'action_desc'=>"厅主{$user->user_name}修改代理商{$request->input('real_name')}账号种类为{$accounType}",
            'action_passivity'=>$request->input('real_name')
        ]);

        //操作成功返回
        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.save_success'),
            'result' => '',
        ]);

    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Patch(
     *   path="/agent/{agent_id}/emailTel",
     *   tags={"代理管理"},
     *   summary="修改手机&邮箱",
     *   description="
     *   修改手机&邮箱
     *   成功返回字段说明
    {
    'code': 0,
    'text': '保存成功',
    'result': ''
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
     *     name="locale",
     *     type="string",
     *     description="语言",
     *     required=true,
     *     default="cn"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="email",
     *     type="string",
     *     description="邮箱",
     *     required=true,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="tel",
     *     type="string",
     *     description="手机号",
     *     required=true,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="tel_pre",
     *     type="string",
     *     description="手机前缀",
     *     required=false,
     *     default=""
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
    public function setEmailTel(Request $request, int $agent_id)
    {

        if($this->agentInfo['grade_id'] == 2) {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.grade_id_error'),
                'result' => '',
            ]);
        }

        $attributes['email'] = $request->input('email');
        $attributes['tel_pre'] = $request->input('tel_pre');
        $attributes['tel'] = (string)$request->input('tel');

        $where = ['grade_id' => 2, 'id' => $agent_id];
        if( ! $user = Agent::where($where)->first() ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.agent_not_exist'),
                'result' => '',
            ]);
        }
        $message = [
            'tel.required' => trans('agent.tel.required'),
            'email.required' => trans('agent.email.required'),
            'email.email' => trans('agent.email.email'),
            'email.unique' => trans('agent.email.unique'),
        ];
        $validator = \Validator::make($request->input(), [
            'tel' => 'required',
            'email' => 'required|email|unique:lb_agent_user,email,'.$agent_id,

        ],$message);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        $re = Agent::where($where)->update($attributes);
        if( $re !== false) {
            $agent_name = '代理商';
            @addLog([
                'action_name'=>'修改'.$agent_name.'的邮箱、手机号',
                'action_desc'=>$this->agentInfo['user_name'].' 对 '.$agent_name.$user['user_name'].'的邮箱、手机号进行修改',
                'action_passivity'=> $user['user_name']
            ]);
            return $this->response->array([
                'code'=>0,
                'text'=> trans('agent.success'),
                'result'=>'',
            ]);

        }

        return $this->response->array([
            'code' => 400,
            'text' => trans('agent.save_fails'),
            'result' => '',
        ]);

    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Patch(
     *   path="/agent/{agent_id}/locking",
     *   tags={"代理管理"},
     *   summary="修改锁定状态&原因",
     *   description="
     *   修改锁定状态&原因
     *   成功返回字段说明
    {
    'code': 0,
    'text': '保存成功',
    'result': ''
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
     *     name="locale",
     *     type="string",
     *     description="语言",
     *     required=true,
     *     default="cn"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="account_lock",
     *     type="string",
     *     description="锁定 1：锁定 0不锁定*",
     *     required=true,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="lock_reason",
     *     type="string",
     *     description="锁定原因",
     *     required=true,
     *     default=""
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
    public function setLock(Request $request,  int $agent_id)
    {

        if($this->agentInfo['grade_id'] == 2) {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.grade_id_error'),
                'result' => '',
            ]);
        }
        $attributes['account_lock'] = $request->input('account_lock', 0);
        $attributes['lock_reason'] = $request->input('lock_reason');

        $where = ['grade_id' => 2, 'id' => $agent_id];
        if( ! $user = Agent::where($where)->first() ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.agent_not_exist'),
                'result' => '',
            ]);
        }

        $re = Agent::where($where)->update($attributes);
        if( $re !== false) {

            //其下级玩家进行锁定或解锁
            $status = $attributes['account_lock'] == 0 ? 1 : 3;//玩家状态：1启用，3禁用
            $this->lockUser($status, $agent_id, 2);

            //更新代理商白名单缓存
            self::storeAgentWhitelist($user['parent_id'], $agent_id);

            $agent_name ='代理商';
            @addLog([
                'action_name'=>'修改'.$agent_name.'的状态',
                'action_desc'=>$this->agentInfo['user_name'].' 对 '.$agent_name.$user['user_name'].'的状态进行修改，状态被置为'.$attributes['account_lock'],
                'action_passivity'=> $user['user_name']
            ]);
            return $this->response->array([
                'code'=>0,
                'text'=> trans('agent.success'),
                'result'=>'',
            ]);

        }

        return $this->response->array([
            'code' => 400,
            'text' => trans('agent.save_fails'),
            'result' => '',
        ]);

    }


    //存储代理商agent_code和对应的厅主白名单信息
    public static function storeAgentWhitelist($hall_id=0, $agent_id=0){
        if( !$hall_id) {
            return false;
        }
        $whiteInfo = Whitelist::where('agent_id',$hall_id)->where("state",1)->first();
        if( $whiteInfo ) {
            $whiteInfo = StringShiftToInt($whiteInfo->toArray(), ['agent_id','state']);
        }

        $where = [
            "grade_id" => 2,
            "is_hall_sub" => 0,
            "parent_id" => $hall_id,
        ];

        $agent_id && $where['id'] = $agent_id;

        $redis_name = "agentWhitelist";

        $agentInfo = Agent::select("id","user_name","agent_code","account_state","account_lock","account_type")->where($where)->get()->toArray();
        if ( ! $agentInfo) {
            return false;
        }
        $data = [];

        $redis = Redis::connection("default");

        foreach (StringShiftToInt($agentInfo,["id","account_state","account_lock","account_type"]) as $k => $v){
            if ($whiteInfo) {

                if ($v["account_state"] == 1){
                    $whiteInfo["agent_code"] = $v["agent_code"];//代理商code
                    $whiteInfo["account_type"] = $v["account_type"];//账号种类,1为正常账号,2为测试账号，3为调试账号
                    $whiteInfo["agent_id2"] = $v["id"];//代理商id
                    $whiteInfo["account_lock"] = $v["account_lock"];//代理商锁定状态
                    $data[$v["user_name"]] = json_encode($whiteInfo);
                } else {
                    //删除不正常的代理商
                    if ($redis->hexists($redis_name,$v["user_name"])) {
                        $redis->hdel($redis_name,$v["user_name"]);
                    }
                }
            } else {
                //删除不正常的代理商
                if ($redis->hexists($redis_name,$v["user_name"])) {
                    $redis->hdel($redis_name,$v["user_name"]);
                }
            }


        }
        $data && $redis->hmset($redis_name,$data);
        return true;
    }

    /**锁定其下级玩家
     * @param int $status 1启用 3禁用
     * @param int $agent_id 代理id
     * @param int $grade_id 代理类型 1厅主， 2代理
     * @return bool
     */
    private function lockUser( int $status, int $agent_id, int $grade_id)
    {
        $where = [];

        switch ($grade_id) {
            case 1:
                $where['hall_id'] = $agent_id;
                break;
            case 2:
                $where['agent_id'] = $agent_id;
                break;
            default:
                return false;
        }
        //查看旗下是否有用户，没有的则进行返回真，有的才进行修改用户状态操作
        $count = Player::where($where)->count();
        if($count)
        {
            return Player::where($where)->update(['account_state' => $status]);
        }
        return true;

    }

    //厅主修改代理商密码
    public function editAgentPwd(Request $request, $agent_id)
    {
        $validator = \Validator::make($request->input(), [
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required|min:6',
            'grade_id' => 'required|integer|in:1,2',
        ]);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }
        $grade_id = $request->input('grade_id');
        $password = $request->input('password');

        $angent_info = Agent::find($agent_id);

        if( ! $angent_info ) {

            return $this->response->array([
                'code'=>400,
                'text'=>trans('agent.agent_not_exist'),
                'result'=>'',
            ]);

        }

        $password = app('hash')->make($password.$angent_info->salt);

        $re = Agent::where(['id'=>$agent_id,'grade_id'=>$grade_id])->update(['password'=>$password]);
        if($re){
            return $this->response->array([
                'code'=>0,
                'text'=>trans('agent.success'),
                'result'=>'',
            ]);
        } else {
            return $this->response->array([
                'code'=>400,
                'text'=>trans('agent.save_fails'),
                'result'=>'',
            ]);
        }
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="agent/menu",
     *   tags={"代理管理"},
     *   summary="获取代理商菜单权限列表数据",
     *   description="
     *   获取代理商菜单权限列表数据
     *   成功返回字段说明
    {
    'code': 0,
    'text': '保存成功',
    'result': ''
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
     *     name="locale",
     *     type="string",
     *     description="语言",
     *     required=true,
     *     default="cn"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="agent_id",
     *     type="string",
     *     description="代理商id",
     *     required=true,
     *     default=""
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
    public function getAgentMenu(Request $request)
    {
        if($this->agentInfo['grade_id'] == 2) {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.grade_id_error'),
                'result' => '',
            ]);
        }

        $grade_id = 2;
        $agent_id  = (int)$request->input('agent_id');
        //获取当前代理商类型所属的菜单列表[menu_id，parent_id]
        $agent_menu_list = AgentMenuList::select('menu_id','parent_id')->where('grade_id',$grade_id)->where('state',1)->get();
        //把菜单menu_id,parent_id存到一个数组里面
        $menu_list = [];
        if( $agent_menu_list ) {
            foreach ($agent_menu_list as $item) {
                $menu_list[] = $item['menu_id'];
                $menu_list[] = $item['parent_id'];
            }
            //去重
            $menu_list = array_unique($menu_list);
        }
        //获取总菜单且属于该代理商类型的菜单
        $menus = AgentMenu::where('state' ,1)->whereIn('id', $menu_list)->get();
        //声明一个菜单id数组
        $menu_ids = [];
        //具体代理商
        if ($agent_id ) {
            //根据代理商id获取所属的菜单数据['menu_id','parent_id']
            $AgentMenus = AgentMenus::select('menu_id','parent_id')->where('user_id', $agent_id)->get();
            foreach ($AgentMenus as $item) {
                $menu_ids[] = $item['menu_id'];
                $menu_ids[] = $item['parent_id'];
            }
            //存到菜单id数组，并去重
            $menu_ids = array_unique($menu_ids);

        }
        //在总菜单处理该代理商相对应的菜单上加字段标识，方便前端显示
        $menus = $menus->each(function ($item) use($menu_ids, $agent_id) {

            if( count($menu_ids) ) {
                if( in_array($item['id'], $menu_ids)) {
                    $item['is_have'] = 1;
                } else {
                    $item['is_have'] = 0;
                }
            } else {
                if( ! $agent_id ) {
                    $item['is_have'] = 1;
                } else {
                    $item['is_have'] = 0;
                }
            }

        });
        return $this->response->array([
            'code'=>0,
            'text'=>trans('agent.success'),
            'result'=>['data' => list_to_tree($menus->toArray(), 'id','parent_id')],
        ]);
    }
}