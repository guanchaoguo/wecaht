<?php
/**
 * 玩家控制器
 * Created by PhpStorm.
 * User: chensongjian
 * Date: 2017/3/31
 * Time: 16:41
 */

namespace App\Http\Controllers\V1;

use App\Http\Controllers\V1\GameServerController;
use Illuminate\Http\Request;
use App\Models\Player;
use App\Models\Agent;
use App\Models\CashRecord;
use App\Models\PlayerOrder;
use App\Models\UserChartInfo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class PlayerController extends BaseController
{

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/player",
     *   tags={"玩家管理"},
     *   summary="玩家列表",
     *   description="
     *   获取玩家列表
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': {
    'total': 1,//总条数
    'per_page': 15,//每页显示条数
    'current_page': 1,//当前页
    'last_page': 1,//上一页
    'next_page_url': null,//下一页url
    'prev_page_url': null,//前一页url
    'data': [
    {
    'uid': 1093,//玩家id
    'username_md': 'tgpxd9ACZV',//玩家在平台的账号
    'alias': 'test member',//昵称
    'agent_name': 'agent_test',//所属代理商
    'add_date': '2017-03-31 21:10:43',//添加日期
    'money': '2000.00',//余额
    'connect_mode': 1, //值为1则为共享钱包模式
    }
    ]
    }
    }",
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="Accept",
     *     type="string",
     *     description="http头信息 *",
     *     required=true,
     *     default="Accept:application/vnd.agent.v1+json"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="token",
     *     type="string",
     *     description="token *",
     *     required=true,
     *     default="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vYWdlbnQudmEvYXBpL2F1dGhvcml6YXRpb24iLCJpYXQiOjE0OTEwMTEzNDEsImV4cCI6MTQ5MTIyNzM0MSwibmJmIjoxNDkxMDExMzQxLCJqdGkiOiJCdno0UzV5S3cyOVFpcTlmIiwic3ViIjoxfQ.--iIUXplgkrUJbigugamkK8f9HnwzSFuO7fehTDfVjQ"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="locale",
     *     type="string",
     *     description="语言",
     *     required=false,
     *     default="zh-cn"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="username_md",
     *     type="string",
     *     description="登录名",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="uid",
     *     type="integer",
     *     description="用户id",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="start_add_time",
     *     type="string",
     *     description="开始时间，格式:2017-03-31 21:10:43",
     *     required=false,
     *     default="2017-03-31 21:10:43"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="end_add_time",
     *     type="string",
     *     description="结束时间，格式:2017-03-31 21:10:43",
     *     required=false,
     *     default="2017-03-31 21:10:44"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="account_state",
     *     type="integer",
     *     description="账号状态,1为正常（启用）,2为暂停使用（冻结）,3为停用, 4登出，5逻辑删除",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="page_num",
     *     type="integer",
     *     description="每页显示条数，默认10",
     *     required=false,
     *     default="10"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="page",
     *     type="integer",
     *     description="当前页数，默认1",
     *     required=false,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="is_page",
     *     type="integer",
     *     description="是否分页，1分页，0不分页，默认1",
     *     required=false,
     *     default="1"
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function index(Request $request)
    {
        $username_md = $request->input('username_md');
        $uid = $request->input('uid');
        $start_add_time = $request->input('start_add_time');
        $end_add_time = $request->input('end_add_time');
        $account_state = $request->input('account_state');
        $page_num = $request->input('page_num',env('PAGE_NUM'));
        $is_page = $request->input('is_page',1);

        $field = ['lb_user.uid','lb_user.user_name','lb_user.username_md','lb_user.alias','lb_user.agent_name','lb_user.add_date','lb_user.agent_name','lb_user.money','lb_user.account_state','lb_user.on_line','au.connect_mode'];
        $db = Player::select($field)->leftJoin("lb_agent_user as au",function($join){
            $join->on('lb_user.hall_id','=','au.id');
        });;

        switch ($this->agentInfo['grade_id']) {
            //厅主
            case 1:
                if( ! $this->agentInfo['is_hall_sub'] ) {
                    //厅主账号
                    $db->where('lb_user.hall_id', $this->agentInfo['id']);
                } else {
                    //厅主子账号，数据查询要关联对应的主厅账号
                    $db->where('lb_user.hall_id', $this->agentInfo['parent_id']);
                }
                break;
            //代理
            case 2:
                $db->where('lb_user.agent_id', $this->agentInfo['id']);
                break;
        }

        if(isset($username_md) && !empty($username_md)) {
//            $db->where('username_md', 'like','%'.decrypt_($username_md).'%');
            $db->where('lb_user.username_md', decrypt_($username_md));
        }

        if(isset($uid) && !empty($uid)) {
            $db->where('lb_user.uid',$uid);
        }
        if(isset($start_add_time) && !empty($start_add_time)) {
            $db->where('lb_user.add_date', '>=', $start_add_time);
        }
        if(isset($end_add_time) && !empty($end_add_time)) {
            $db->where('lb_user.add_date', '<', $end_add_time);
        }
        if(isset($account_state) && $account_state !== '') {
            $db->where('lb_user.account_state',$account_state);
        }

        $db->orderby('lb_user.add_date','desc');

        if($is_page) {
            $player = $db->paginate($page_num);
        } else {
            $player = $db->get();
        }

        foreach ($player as &$v) {
            $v->user_name = encrypt_($v->user_name);
            $v->username_md = encrypt_($v->username_md);
        }


        return $this->response->array([
            'code' => 0,
            'text' =>trans('agent.success'),
            'result' => $is_page ? $player : ['data' => $player],
        ]);
    }


    /**
     * consumes={"multipart/form-data"},
     * @SWG\Post(
     *   path="/player",
     *   tags={"玩家管理"},
     *   summary="添加玩家",
     *   description="
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': ''
     }",
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="Accept",
     *     type="string",
     *     description="http头信息 *",
     *     required=true,
     *     default="Accept:application/vnd.agent.v1+json"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="token",
     *     type="string",
     *     description="token *",
     *     required=true,
     *     default="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vYWdlbnQudmEvYXBpL2F1dGhvcml6YXRpb24iLCJpYXQiOjE0OTEwMTEzNDEsImV4cCI6MTQ5MTIyNzM0MSwibmJmIjoxNDkxMDExMzQxLCJqdGkiOiJCdno0UzV5S3cyOVFpcTlmIiwic3ViIjoxfQ.--iIUXplgkrUJbigugamkK8f9HnwzSFuO7fehTDfVjQ"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="locale",
     *     type="string",
     *     description="语言",
     *     required=false,
     *     default="zh-cn"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="username_md",
     *     type="string",
     *     description="登录名 *",
     *     required=true,
     *     default="ancsj_play11"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="alias",
     *     type="string",
     *     description="昵称",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="password_md",
     *     type="string",
     *     description="密码 *",
     *     required=true,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="password_md_confirmation",
     *     type="string",
     *     description="确认密码 *",
     *     required=true,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="agent_name",
     *     type="string",
     *     description="代理名称 ps:厅主添加玩家时需要，代理添加玩家不需要",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="account_state",
     *     type="integer",
     *     description="账号状态,1为正常 * （启用）,2为暂停使用（冻结）,3为停用, 4登出，5逻辑删除",
     *     required=true,
     *     default=""
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function store(Request $request)
    {
        $message = [];
        $validator = \Validator::make($request->input(), [
            'username_md' => 'required',
//            'username_md' => 'required|unique:lb_user,username_md',
            'password_md' => 'required|max:6|confirmed',
            'password_md_confirmation' => 'required|max:6',
            'account_state' => 'required',
        ],$message);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }
        $username_md = $request->input('username_md');
        //添加玩家时用户名过滤
        if(!preg_match('/^[a-zA-z][a-zA-Z0-9_]{5,19}$/',$username_md)) {
            return $this->response->array([
                'code'=>400,
                'text'=>trans('agent.user_name'),
                'result'=>'',
            ]);
        }


        if( Player::where('user_name',decrypt_($username_md))->first() ) {
            return $this->response->array([
                'code'=>400,
                'text'=>trans('agent.user_has_exist'),
                'result'=>'',
            ]);
        }

        $agent_name = $request->input('agent_name');


        $salt = randomkeys(20);
        $attributes = [
//            'user_name' => decrypt_($Prefix.$request->input('username_md')),
            'username_md' => decrypt_($request->input('username_md')),
            'password' => decrypt_($request->input('password_md')),
            'password_md' => decrypt_($request->input('password_md')),
            'account_state' => $request->input('account_state'),
            'add_ip' => $request->ip(),
            'salt' => randomkeys(20),
            'alias' => $request->input('alias') ?? '',
            'create_time'   => date('Y-m-d H:i:s',time()),
            'last_time'     => date('Y-m-d H:i:s',time()),
        ];

        switch ($this->agentInfo['grade_id']) {
            //厅主
            case 1:
                $fields = ['id','user_name','grade_id','is_hall_sub','parent_id','agent_code'];
                $agent = Agent::select($fields)->where('user_name', $agent_name)->first();
                if($agent['grade_id'] != 2 || $agent['is_hall_sub'] != 0 || $agent['parent_id'] != $this->agentId ) {
                    return $this->response->array([
                        'code'=>400,
                        'text'=>trans('agent.agent_not_exist'),
                        'result'=> '',
                    ]);
                }
                //代理商
                $attributes['agent_id'] = $agent->id;
                $attributes['agent_name'] = $agent->user_name;
                $attributes['user_name'] = decrypt_($agent->agent_code.$request->input('username_md'));
                //厅主
                if( ! $this->agentInfo['is_hall_sub'] ) {
                    //厅主账号
                    $attributes['hall_id'] = $this->agentInfo['id'];
                    $attributes['hall_name'] = $this->agentInfo['user_name'];
                } else {
                    //厅主子账号
                    $hall = Agent::select($fields)->where('id', $this->agentInfo['parent_id'])->first();
                    $attributes['hall_id'] = $hall['id'];
                    $attributes['hall_name'] = $hall['user_name'];
                }
                break;
            //代理
            case 2:
                $attributes['agent_id'] = $this->agentInfo['id'];
                $attributes['agent_name'] = $this->agentInfo['user_name'];
                $attributes['user_name'] = decrypt_($this->agentInfo['agent_code'].$request->input('username_md'));

                $fields = ['id','user_name'];
                $hall = Agent::select($fields)->where('id', $this->agentInfo['parent_id'])->first();
                $attributes['hall_id'] = $hall['id'];
                $attributes['hall_name'] = $hall['user_name'];
                break;
        }

        $user = Player::create($attributes);
        if( !$user ){
            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.add_fails'),
                'result' => '',
            ]);
        }

        //所属的厅主、代理相应累计玩家数
        Agent::where('id',$attributes['hall_id'])->increment('sub_user');
        Agent::where('id',$attributes['agent_id'])->increment('sub_user');

        return $this->response->array([
            'code' => 0,
            'text' =>trans('agent.success'),
            'result' => '',
        ]);
    }


    /**
     * consumes={"multipart/form-data"},
     * @SWG\Put(
     *   path="/player/1094",
     *   tags={"玩家管理"},
     *   summary="编辑保存玩家",
     *   description="
     *  /player/{id} ,{id}为玩家id
     *   成功返回字段说明
    {
    'code': 0,
    'text': '保存成功',
    'result': ''
    }",
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="Accept",
     *     type="string",
     *     description="http头信息 *",
     *     required=true,
     *     default="Accept:application/vnd.agent.v1+json"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="token",
     *     type="string",
     *     description="token *",
     *     required=true,
     *     default="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vYWdlbnQudmEvYXBpL2F1dGhvcml6YXRpb24iLCJpYXQiOjE0OTEwMTEzNDEsImV4cCI6MTQ5MTIyNzM0MSwibmJmIjoxNDkxMDExMzQxLCJqdGkiOiJCdno0UzV5S3cyOVFpcTlmIiwic3ViIjoxfQ.--iIUXplgkrUJbigugamkK8f9HnwzSFuO7fehTDfVjQ"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="locale",
     *     type="string",
     *     description="语言",
     *     required=false,
     *     default="zh-cn"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="alias",
     *     type="string",
     *     description="昵称",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="agent_id",
     *     type="integer",
     *     description="代理id ps:厅主添加玩家时需要，代理添加玩家时不需要",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="account_state",
     *     type="integer",
     *     description="账号状态 * ,1为正常（启用）,2为暂停使用（冻结）,3为停用, 4登出，5逻辑删除",
     *     required=true,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="language",
     *     type="string",
     *     description="玩家选择的语言 *",
     *     required=true,
     *     default=""
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function update(Request $request, int $id)
    {
        $message = [];
        $validator = \Validator::make($request->input(), [
            'account_state' => 'required',
        ],$message);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        $attributes = [
            'account_state' => $request->input('account_state'),
            'alias' => $request->input('alias'),
            //'last_time'     => date('Y-m-d H:i:s',time()),
        ];




        switch ($this->agentInfo['grade_id']) {
            //厅主
            case 1:
                $agent_id = $request->input('agent_id');
                $player = Player::select('uid','user_name','hall_id','agent_id')->where(['uid' => $id, 'hall_id'=>$this->agentId])->first();
                if( ! $player ) {
                    return $this->response->array([
                        'code'=>400,
                        'text'=>trans('agent.user_not_exist'),
                        'result'=> '',
                    ]);
                }
                if($player['agent_id'] != $agent_id) {
                    $fields = ['id','user_name','grade_id','is_hall_sub','parent_id'];
                    $agent = Agent::select($fields)->where('id', $agent_id)->first();
                    if($agent['grade_id'] != 2 || $agent['is_hall_sub'] != 0 || $agent['parent_id'] != $this->agentId ) {
                        return $this->response->array([
                            'code'=>400,
                            'text'=>trans('agent.agent_not_exist'),
                            'result'=> '',
                        ]);
                    }
                    //代理商
                    $attributes['agent_id'] = $agent->id;
                    $attributes['agent_name'] = $agent->user_name;
                    //厅主
                    if( ! $this->agentInfo['is_hall_sub'] ) {
                        //厅主账号
                        $attributes['hall_id'] = $this->agentInfo['id'];
                        $attributes['hall_name'] = $this->agentInfo['user_name'];
                    } else {
                        //厅主子账号
                        $hall = Agent::select($fields)->where('id', $this->agentInfo['parent_id'])->first();
                        $attributes['hall_id'] = $hall['id'];
                        $attributes['hall_name'] = $hall['user_name'];
                    }
                }
                break;
            //代理
            case 2:
                $player = Player::select('uid','user_name', 'hall_id','agent_id')->where(['uid' => $id, 'agent_id'=>$this->agentId])->first();
                if( ! $player ) {
                    return $this->response->array([
                        'code'=>400,
                        'text'=>trans('agent.user_not_exist'),
                        'result'=> '',
                    ]);
                }
                break;
        }

        $user = Player::where('uid',$id)->update($attributes);

        if( $user === false) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.save_fails'),
                'result' => '',
            ]);
        }

        if( isset($attributes['agent_id'])  && isset($attributes['hall_id'] )) {
            Agent::where('id',$player['agent_id'])->decrement('sub_user');
            Agent::where('id',$player['hall_id'])->decrement('sub_user');

            Agent::where('id',$attributes['agent_id'])->increment('sub_user');
            Agent::where('id',$attributes['hall_id'])->increment('sub_user');
        }

        self::updatePlayerInfoRedis($id);


      // 添加操作日志
        $userName = encrypt_($player['user_name']);
      @addLog([
          'action_name'=> '编辑玩家信息',
          'action_desc'=> "编辑玩家信息; 名称{$userName} ID{$id}",
           'action_passivity'=>$userName
       ]);


        return $this->response->array([
            'code' => 0,
            'text' =>trans('agent.save_success'),
            'result' => '',
        ]);
    }

    /**
     * 更新玩家redis信息
     * @param int $id 玩家id
     */
    private function updatePlayerInfoRedis(int $id)
    {
        $info = Player::find($id);
        if($info) {
            $info = StringShiftToInt($info->toArray(),['user_rank','account_state','hall_id','agent_id','profit_share_platform','profit_share_agent','profit_share_hall','money','grand_total_money']);
        }
        $agent = Agent::select('agent_code')->find($info['agent_id']);
        $session_id = md5( $info['user_name'] );
        $uid = substr( $session_id, 0, 21 );
        $user_name = $info['user_name'];
        $info['user_name'] = encrypt_($user_name);
        $info['username_md'] = encrypt_($info['username_md']);
        $info['username2'] = $user_name;
        $info['time'] = time();
        $info['agent_code'] = $agent['agent_code'];
        $redis = Redis::connection("account");
        if( $re = $redis->get($uid) ) {
            $redis->set($uid, json_encode($info));
        }
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/player/1094",
     *   tags={"玩家管理"},
     *   summary="获取玩家详情",
     *   description="
     *  /player/{id} ,{id}为玩家id
     *   成功返回字段说明
        {'code': 0,
        'text': '操作成功',
        'result': {
        'uid': 1094,//玩家id
        'user_name': 'ancsj_play11',//玩家在第三方平台账号
        'username_md': 'ancsj_play11',//玩家在平台的账号
        'alias': '12d',//玩家昵称
        'on_line': 'N',//是否在线
        'agent_id': 9,//代理商ID
        'hall_name': 'csj',//厅主名称
        'agent_name': 'anchen2',//代理商名称
        'money': '11000.00',//当前用户余额
        'language': 'tttttt',//玩家选择的语言
        'account_state': 13//账号状态,1为正常（启用）,2为暂停使用（冻结）,3为停用, 4登出，5逻辑删除
        }
        }",
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="Accept",
     *     type="string",
     *     description="http头信息 *",
     *     required=true,
     *     default="Accept:application/vnd.agent.v1+json"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="token",
     *     type="string",
     *     description="token *",
     *     required=true,
     *     default="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vYWdlbnQudmEvYXBpL2F1dGhvcml6YXRpb24iLCJpYXQiOjE0OTEwMTEzNDEsImV4cCI6MTQ5MTIyNzM0MSwibmJmIjoxNDkxMDExMzQxLCJqdGkiOiJCdno0UzV5S3cyOVFpcTlmIiwic3ViIjoxfQ.--iIUXplgkrUJbigugamkK8f9HnwzSFuO7fehTDfVjQ"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="locale",
     *     type="string",
     *     description="语言",
     *     required=false,
     *     default="zh-cn"
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function show(Request $request, int $id)
    {
        $field = ['uid','user_name','username_md','alias','on_line','agent_id','hall_name','agent_name','money','language','account_state'];
        $where = [
            'uid' => $id,
        ];

        switch ($this->agentInfo['grade_id']) {
            case 1:
                $where['hall_id'] = $this->agentId;
                break;
            case 2:
                $where['agent_id'] = $this->agentId;
                break;
        }

        $player = Player::select($field)->where($where)->first();

        if( !$player ) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.user_not_exist'),
                'result' =>'',
            ]);
        }
        $player->account_state = (string)$player->account_state;
        $player->agent_id = (string)$player->agent_id;
        $player->user_name = encrypt_($player->user_name);
        $player->username_md = encrypt_($player->username_md);
        return $this->response->array([
            'code' => 0,
            'text' =>trans('agent.success'),
            'result' =>  $player,
        ]);
    }


    /**
     * consumes={"multipart/form-data"},
     * @SWG\Patch(
     *   path="/player/1094/password",
     *   tags={"玩家管理"},
     *   summary="修改玩家密码",
     *   description="
     *  /player/{id}/password ,{id}为玩家id
     *   成功返回字段说明
    {'code': 0,
    'text': '操作成功',
    'result': ''
    }",
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="Accept",
     *     type="string",
     *     description="http头信息 *",
     *     required=true,
     *     default="Accept:application/vnd.agent.v1+json"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="token",
     *     type="string",
     *     description="token *",
     *     required=true,
     *     default="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vYWdlbnQudmEvYXBpL2F1dGhvcml6YXRpb24iLCJpYXQiOjE0OTEwMTEzNDEsImV4cCI6MTQ5MTIyNzM0MSwibmJmIjoxNDkxMDExMzQxLCJqdGkiOiJCdno0UzV5S3cyOVFpcTlmIiwic3ViIjoxfQ.--iIUXplgkrUJbigugamkK8f9HnwzSFuO7fehTDfVjQ"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="locale",
     *     type="string",
     *     description="语言",
     *     required=false,
     *     default="zh-cn"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="password_md",
     *     type="string",
     *     description="新密码",
     *     required=true,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="locale",
     *     type="string",
     *     description="确认新密码",
     *     required=true,
     *     default=""
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function password(Request $request, int $id)
    {
        $validator = \Validator::make($request->input(), [
            'password_md' => [
                'required',
                'min:6',
                'max:12',
                'regex:/^[0-9a-zA-Z]{6,12}$/',
                'confirmed'
            ],
            'password_md_confirmation' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        $where = [
            'uid' => $id,
        ];

        switch ($this->agentInfo['grade_id']) {
            case 1:
                $where['hall_id'] = $this->agentId;
                break;
            case 2:
                $where['agent_id'] = $this->agentId;
                break;
        }

        $player = Player::select('uid','salt','user_name')->where($where)->first();

        if( !$player ) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.user_not_exist'),
                'result' =>'',
            ]);
        }

        if( ! $salt = $player->salt ) {
            $salt = randomkeys(20);
            Player::where($where)->update(['salt'=>$salt]);
        }

//        $password = sha1($request->input('password_md').$salt);
        $password = decrypt_($request->input('password_md'));
        $re = Player::where($where)->update(['password'=>$password,'password_md' => $password]);

        if($re !== false){

            self::updatePlayerInfoRedis($id);


         //添加操作日志
        $userName = encrypt_($player->user_name);
        @addLog([
            'action_name'=> '修改玩家密码',
            'action_desc'=> "修改玩家密码 名称 {$userName}ID:{$id}",
            'action_passivity'=>$userName
        ]);


            return $this->response->array([
                'code'=>0,
                'text'=>trans('agent.save_success'),
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
     * @SWG\Patch(
     *   path="/player/1094/balance",
     *   tags={"玩家管理"},
     *   summary="修改玩家余额",
     *   description="
     *  /player/{id}/balance ,{id}为玩家id
     *   成功返回字段说明
    {'code': 0,
    'text': '操作成功',
    'result': ''
    }",
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="Accept",
     *     type="string",
     *     description="http头信息 *",
     *     required=true,
     *     default="Accept:application/vnd.agent.v1+json"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="token",
     *     type="string",
     *     description="token *",
     *     required=true,
     *     default="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vYWdlbnQudmEvYXBpL2F1dGhvcml6YXRpb24iLCJpYXQiOjE0OTEwMTEzNDEsImV4cCI6MTQ5MTIyNzM0MSwibmJmIjoxNDkxMDExMzQxLCJqdGkiOiJCdno0UzV5S3cyOVFpcTlmIiwic3ViIjoxfQ.--iIUXplgkrUJbigugamkK8f9HnwzSFuO7fehTDfVjQ"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="locale",
     *     type="string",
     *     description="语言",
     *     required=false,
     *     default="zh-cn"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="money",
     *     type="number",
     *     description="金额",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="status",
     *     type="integer",
     *     description="加减状态，3是加，4是减",
     *     required=true,
     *     default="3"
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function balanceUpdate(Request $request, int $id)
    {
        $validator = \Validator::make($request->input(), [
            'money' => 'required|numeric|min:1',
            'status' => 'required|in:3,4',
        ]);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        $where = [
            'uid' => $id,
        ];
        $agent_type = '';
        switch ($this->agentInfo['grade_id']) {
            case 1:
                $agent_type = '厅主';
                $where['hall_id'] = $this->agentId;
                break;
            case 2:
                $agent_type = '代理商';
                $where['agent_id'] = $this->agentId;
                break;
        }

        $user = Player::where($where)->first();

        if( !$user ) {

            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.user_not_exist'),
                'result'=>'',
            ]);

        }
        $status = $request->input('status');
        $money = sprintf("%.2f", $request->input('money'));
        $re = '';
        switch ($status) {
            //充值金额
            case 3:


                $re = Player::where($where)->increment('money', $money);

                //累计充值余额
                $re && Player::where($where)->update(['grand_total_money' => $user->money + $money]);

                //统计代理商下的玩家充值
                $re && $this->totalScoreRecord($user->agent_id, $money);
                break;
            //扣取金额
            case 4:
                if( ($user->money - $money) < 0) {
                    return $this->response->array([
                        'code'=>400,
                        'text'=> trans('agent.insufficient_balance'),
                        'result'=>'',
                    ]);
                }
                $re = Player::where($where)->decrement('money', $money);
                //累计扣款余额
                $re && Player::where($where)->update(['grand_total_money' => $user->money - $money]);
                break;
        }

        $agent = Agent::where('id',$user->agent_id)->pluck('user_name');
        $agent_name = $agent ? $agent[0] : '';

        if( $re ) {
            //用户增加充值、扣款后累计清除下注次数
            $redis = Redis::connection("monitor");
            $redis->set("betcount:".$id, 0);
            //重新获取玩家余额
            $user_money = Player::where($where)->pluck('money')[0];

            $cashRecord = new CashRecord;
            $ordernum = createOrderSn();
            $cashRecord->order_sn = $ordernum;
            $cashRecord->cash_no = $ordernum;
            $cashRecord->uid = (int) $user->uid;
            $cashRecord->agent_id = (int) $user->agent_id;
            $cashRecord->hall_id = (int) $user->hall_id;
            $cashRecord->user_name = encrypt_($user->user_name);//玩家在第三方平台账号
            $cashRecord->type = 4;
            $cashRecord->amount = (double) $money;
            $cashRecord->status = $status;
            $cashRecord->user_money = (double)$user_money;
//            $cashRecord->desc = $status == 3 ? $agent_type.'为用户余额充值' : $agent_type.'为用户余额扣取';
            $cashRecord->desc = '流水号：'.$ordernum;
            $cashRecord->admin_user = $this->agentInfo['user_name'];
            $cashRecord->admin_user_id = (int) $this->agentInfo['id'];
            $cashRecord->add_time = new \MongoDB\BSON\UTCDateTime(time() * 1000);
            $cashRecord->pkey = md5($agent_name.$ordernum.env('PT_API_SUF'));
            $cashRecord->save();

            self::updatePlayerInfoRedis($id);

            // 添加操作日志
            @addLog([
                'action_name'=> '修改玩家余额',
                'action_desc'=> "修改玩家余额; 名称{$cashRecord->user_name} " .$cashRecord->desc.':'.$money,
                'action_passivity'=>$cashRecord->user_name
            ]);

            return $this->response->array([
                'code'=>0,
                'text'=> trans('agent.success'),
                'result'=>'',
            ]);
        }



        return $this->response->array([
            'code'=>400,
            'text'=> trans('agent.save_fails'),
            'result'=>'',
        ]);
    }


    /**
     * 玩家充值时 给代理统计
     * @param int $agent_id 代理商id
     * @param float $money 金额
     * @return int
     */
    private function totalScoreRecord( int $agent_id, $money) : int
    {
        $agent = Agent::select('user_name','parent_id','id')->where(['id' => $agent_id])->first();
        if( $agent ) {

            $hall_agent = Agent::select('user_name')->where(['id' => $agent->parent_id])->first();

            $where = [
                'add_date' => date('Y-m-d', time()),
                'agent_id' => $agent->id
            ];
            $re = \DB::table('statis_cash_agent')->where($where)->first();

            if( ! $re ) {

                $where = [
                    'day_year' => date('Y', time()),
                    'day_month' => date('m', time()),
                    'day_day' => date('d', time()),
                    'agent_id' => $agent->id,
                    'add_date' => date('Y-m-d', time()),
                ];

                $where['add_date'] = date('Y-m-d', time());
                $where['hall_id'] = $agent->parent_id;
                $where['agent_name'] = $agent->user_name;
                $where['hall_name'] = $hall_agent->user_name;
                $where['total_score_record'] = $money;

                $res = \DB::table('statis_cash_agent')->insert($where);
                if( $res ) {
                    return 1;
                } else {
                    return -1;
                }
            } else {

                $res = \DB::table('statis_cash_agent')->where($where)->increment('total_score_record', $money);;
                if( $res !== false) {
                    return 1;
                } else {
                    return -1;
                }
            }
        } else {
            return -1;
        }

    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Patch(
     *   path="/player/1094/status",
     *   tags={"玩家管理"},
     *   summary="修改玩家状态（1启用、2冻结、3停用）",
     *   description="
     *  /player/{id}/status ,{id}为玩家id
     *   成功返回字段说明
    {'code': 0,
    'text': '操作成功',
    'result': ''
    }",
     *   operationId="status",
     *   @SWG\Parameter(
     *     in="header",
     *     name="Accept",
     *     type="string",
     *     description="http头信息 *",
     *     required=true,
     *     default="Accept:application/vnd.agent.v1+json"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="token",
     *     type="string",
     *     description="token *",
     *     required=true,
     *     default="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vYWdlbnQudmEvYXBpL2F1dGhvcml6YXRpb24iLCJpYXQiOjE0OTEwMTEzNDEsImV4cCI6MTQ5MTIyNzM0MSwibmJmIjoxNDkxMDExMzQxLCJqdGkiOiJCdno0UzV5S3cyOVFpcTlmIiwic3ViIjoxfQ.--iIUXplgkrUJbigugamkK8f9HnwzSFuO7fehTDfVjQ"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="locale",
     *     type="string",
     *     description="语言",
     *     required=false,
     *     default="zh-cn"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="account_state",
     *     type="integer",
     *     description="状态值",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function statusUpdate(Request $request, int $id)
    {
        $account_state = $request->input('account_state');

        $where = [
            'uid' => $id,
        ];
        switch ($this->agentInfo['grade_id']) {
            case 1:
                $where['hall_id'] = $this->agentId;
                break;
            case 2:
                $where['agent_id'] = $this->agentId;
                break;
        }

        $user = Player::where($where)->first();

        if( !$user ) {

            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.user_not_exist'),
                'result'=>'',
            ]);

        }

        $saveData = [];
        if( !in_array($account_state, [1,2,3]) ) {

            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.param_error'),
                'result'=>'',
            ]);

        }
        $saveData['account_state'] = $account_state;

        /*if($account_state == 4) {
            $saveData['on_line'] = 'N';
        }*/
        $re = Player::where($where)->update($saveData);

        if( $re !== false ){

            self::updatePlayerInfoRedis($id);

            $stat = ['启用','冻结' ,'停用'];
            $statName =  $stat[$account_state];
            $userName = encrypt_($user->user_name);
            @addLog([
                'action_name'=> $statName.'玩家状态',
                'action_desc'=> $statName."玩家状态; 名称{$userName} ID:{$id}",
                'action_passivity'=>$userName
        ]);

            if($account_state == 2) {
                //写入队列中
                $msg = json_encode(['cmd'=>'KickPlayer','accountId'=>$id]);
                $re = RabbitmqController::publishMsg([env('MQ_SERVER_CHANNEL'),env('MQ_SERVER_QUEUE'),env('MQ_SERVER_KEY'),$msg]);

            }

            return $this->response->array([
                'code'=>0,
                'text'=>trans('agent.save_success'),
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
     * @SWG\Patch(
     *   path="/player/{id}/onLine",
     *   tags={"玩家管理"},
     *   summary="玩家登出",
     *   description="
     *   成功返回字段说明
        {'code': 0,
        'text': '操作成功',
        'result': ''
        }",
     *   operationId="status",
     *   @SWG\Parameter(
     *     in="header",
     *     name="Accept",
     *     type="string",
     *     description="http头信息 *",
     *     required=true,
     *     default="Accept:application/vnd.agent.v1+json"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="token",
     *     type="string",
     *     description="token *",
     *     required=true,
     *     default="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vYWdlbnQudmEvYXBpL2F1dGhvcml6YXRpb24iLCJpYXQiOjE0OTEwMTEzNDEsImV4cCI6MTQ5MTIyNzM0MSwibmJmIjoxNDkxMDExMzQxLCJqdGkiOiJCdno0UzV5S3cyOVFpcTlmIiwic3ViIjoxfQ.--iIUXplgkrUJbigugamkK8f9HnwzSFuO7fehTDfVjQ"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="locale",
     *     type="string",
     *     description="语言",
     *     required=false,
     *     default="zh-cn"
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function signOut(Request $request, int $id)
    {
        $where = [
            'uid' => $id,
        ];

        switch ($this->agentInfo['grade_id']) {
            case 1:
                $where['hall_id'] = $this->agentId;
                break;
            case 2:
                $where['agent_id'] = $this->agentId;
                break;
        }

        $user = Player::where($where)->first();

        if( !$user ) {

            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.user_not_exist'),
                'result'=>'',
            ]);

        }

        if( $user->on_line == 'N' ) {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.user_sign_out'),
                'result'=>'',
            ]);
        }
//        $GameServer = new \App\Http\Controllers\V1\GameServerController();
//        $re = $GameServer->userLoginOut($id);
        //写入队列中
        $msg = json_encode(['cmd'=>'KickPlayer','accountId'=>$id]);
        $re = RabbitmqController::publishMsg([env('MQ_SERVER_CHANNEL'),env('MQ_SERVER_QUEUE'),env('MQ_SERVER_KEY'),$msg]);
        if( $re ) {

//            Player::where($where)->update(['on_line' => 'N']);

            self::updatePlayerInfoRedis($id);

            // 添加操作日志
            $userName = encrypt_($user->user_name);
            @addLog([
                'action_name'=>'玩家登出',
                'action_desc'=> "玩家状态; 名称{$userName} ID:{$id}",
                'action_passivity'=>$userName
            ]);

            return $this->response->array([
                'code'=>0,
                'text'=>trans('agent.success'),
                'result'=>'',
            ]);

        }

        return $this->response->array([
            'code'=>400,
            'text'=>trans('agent.fails'),
            'result'=>'',
        ]);
    }

    //此接口弃用
    public function order(Request $request)
    {
        return '此接口弃用';
        $user_name = $request->input('user_name');
        $user_id = (int)$request->input('user_id');
        $_id = $request->input('_id');
        $game_hall_id = $request->input('game_hall_id');
        $game_id = $request->input('game_id');
        $round_no = $request->input('round_no');
        $status = $request->input('status');
        $start_add_time = $request->input('start_add_time');
        $end_add_time = $request->input('end_add_time');
        $page_num = $request->input('page_num',env('PAGE_NUM'));
        $is_page = $request->input('is_page',1);
        $playerOrder = PlayerOrder::select();

        switch ($this->agentInfo['grade_id']) {
            case 1:
                $playerOrder->where('hall_id',$this->agentId);
                break;
            case 2:
                $playerOrder->where('agent_id',$this->agentId);
                break;
        }

        if(isset($user_id) && !empty($user_id)) {
            $playerOrder->where('user_id',$user_id);
        }

        if(isset($user_name) && !empty($user_name)) {

            $playerOrder->where('user_name',$user_name);
        }

        if(isset($_id) && !empty($_id)) {
            $playerOrder->where('cashrecord_id',$_id);
        }

        if(isset($game_hall_id) && $game_hall_id !== '') {

            $playerOrder->where('game_hall_id',(int)$game_hall_id);
        }

        if(isset($round_no) && !empty($round_no)) {

            $playerOrder->where('round_no',$round_no);
        }
        if(isset($game_id) && !empty($game_id)) {

            $playerOrder->where('game_id',(int)$game_id);
        }

        if(isset($status) && !empty($status)) {
            switch ($status) {
                //未取消未派彩
                case 1:
                    $playerOrder->where('is_cancel',0);
                    $playerOrder->where('calculated',0);
                    break;
                //未取消已派彩
                case 2:
                    $playerOrder->where('is_cancel',0);
                    $playerOrder->where('calculated',1);
                    break;
                //已取消未派彩
                case 3:
                    $playerOrder->where('is_cancel',1);
                    $playerOrder->where('calculated',0);
                    break;
                //已取消已派彩
                case 4:
                    $playerOrder->where('is_cancel',1);
                    $playerOrder->where('calculated',1);
                    break;
            }
//            $playerOrder->where('status',(int)$status);
        }
        if(isset($start_add_time) && !empty($start_add_time)) {

            $start_add_time = date("Y-m-d H:i:s",strtotime($start_add_time));
            $playerOrder->where('add_time', '>=', new \DateTime($start_add_time));

        }

        if(isset($end_add_time) && !empty($end_add_time)) {

            $end_add_time = date("Y-m-d H:i:s",strtotime($end_add_time));
            $playerOrder->where('add_time', '<', new \DateTime($end_add_time));

        }

        $playerOrder->orderby('add_time','desc');

        if($is_page) {
            $re = $playerOrder->paginate((int)$page_num);
        } else {
            $re = $playerOrder->get();
        }

        $total_score = [
            'bet_money' => 0,
            'bet_money_valid' => 0,
            'payout_win' => 0,
        ];

        foreach ($re as $v){
            $v->add_time = $v->add_time->__tostring();
            $v->add_time = date('Y-m-d H:i:s',$v->add_time/1000);
            !$v['is_cancel'] && $total_score['bet_money'] += $v->bet_money;
            !$v['is_cancel'] && $total_score['bet_money_valid'] += $v->bet_money_valid;
            !$v['is_cancel'] && $total_score['payout_win'] += $v->payout_win;
            $v->bet_money = number_format($v->bet_money, 2);
            $v->bet_money_valid = number_format($v->bet_money_valid, 2);
            $v->payout_win = number_format($v->payout_win, 2);
            $v->odds = number_format($v->odds, 2);
        }

        if( count($total_score) ) {
            $total_score['bet_money'] = number_format($total_score['bet_money']);
            $total_score['bet_money_valid'] = number_format($total_score['bet_money_valid']);
            $total_score['payout_win'] = number_format($total_score['payout_win']);
        }

        if( $is_page ) {
            $re = $re->toArray();
            $re['total_score'] = $total_score;
        } else {
            $re = ['data' => $re,'total_score'=>$total_score];
        }

        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>$re,
        ]);
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Post(
     *   path="/player/order",
     *   tags={"注单查询"},
     *   summary="注单查询列表",
     *   description="
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': {
    'total': 1,//总条数
    'per_page': 15,//每页显示条数
    'current_page': 1,//当前页
    'last_page': 1,//上一页
    'next_page_url': null,//下一页url
    'prev_page_url': null,//前一页url
    'from': 1,
    'to': 1,
    'data': [
    {
    '_id': '5979ce73e138231a1e43e1f0',//记录id
    'total_bet_score': '1,000.00',//投注额
    'total_win_score': '0.00',//派彩额
    'valid_bet_score_total': '1,000.00',//有效投注额
    'cat_id': 1,//游戏分类id
    'start_time': '2017-07-27 07:28:51',//开始时间（下注时间）
    'server_name': '15',//桌号
    'is_cancel': 1,//是否取消，0：否，1：是
    'round_no': '6638b3a2e92a09d1',//局ID
    'game_period': '67-23',//靴-局信息
    'dwRound': 23,//局信息
    'remark': '1;35',//牌信息（游戏结果）
    'account': 'D01shenwenzhong',//玩家登录名
    'is_mark': 1,//是否派彩,0：否，1：是
    'game_hall_code': 'GH0001',//游戏厅标识码
    'game_name': '龙虎 ',//游戏名称
    'ip_info': '',//ip
    'game_result': '',//游戏结果
    'is_rollback': 0//是否回滚，0：否，1：是
    }
    ],
    'total_score': {
    'total_bet_score': '500',//总投注额
    'valid_bet_score_total': '500',//总有效投注额
    'total_win_score': '0'//总派彩金额
    }
    }
    }",
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="Accept",
     *     type="string",
     *     description="http头信息 *",
     *     required=true,
     *     default="Accept:application/vnd.agent.v1+json"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="token",
     *     type="string",
     *     description="token *",
     *     required=true,
     *     default="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vYWdlbnQudmEvYXBpL2F1dGhvcml6YXRpb24iLCJpYXQiOjE0OTEwMTEzNDEsImV4cCI6MTQ5MTIyNzM0MSwibmJmIjoxNDkxMDExMzQxLCJqdGkiOiJCdno0UzV5S3cyOVFpcTlmIiwic3ViIjoxfQ.--iIUXplgkrUJbigugamkK8f9HnwzSFuO7fehTDfVjQ"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="locale",
     *     type="string",
     *     description="语言",
     *     required=false,
     *     default="zh-cn"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="user_id",
     *     type="integer",
     *     description="用户id",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="account",
     *     type="string",
     *     description="用户登录名",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="game_hall_id",
     *     type="integer",
     *     description="游戏厅id",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="round_no",
     *     type="string",
     *     description="局ID",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="status",
     *     type="integer",
     *     description="状态 1：未取消未派彩，2：未取消已派彩，3：已取消未派彩，4：已取消已派彩",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="start_add_time",
     *     type="string",
     *     description="下注开始时间 格式2017-03-13 17:22:14",
     *     required=false,
     *     default="2017-03-13 17:22:14"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="end_add_time",
     *     type="string",
     *     description="下注结束时间 格式2017-03-13 17:22:15",
     *     required=false,
     *     default="2017-03-13 17:22:15"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="page_num",
     *     type="integer",
     *     description="每页显示条数 默认10",
     *     required=false,
     *     default="10"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="page",
     *     type="integer",
     *     description="当前页 默认1",
     *     required=false,
     *     default="1"
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function userChartInfo(Request $request)
    {
        $user_id = $request->input('user_id');
        $account = $request->input('account');
        $game_hall_id = $request->input('game_hall_id');
        $round_no = $request->input('round_no');
        $status = $request->input('status');
        $start_add_time = $request->input('start_add_time');
        $end_add_time = $request->input('end_add_time');
        $page_num = $request->input('page_num',10);
        $is_page = $request->input('is_page',1);
        $game_period = $request->input('game_period');
        $connect_mode = $request->input('connect_mode');//扣费模式
        $field = [
            '_id',
            'account',
            'total_bet_score',
            'total_win_score',
            'valid_bet_score_total',
            'start_time',
            'end_time',
            'server_name',
            'is_cancel',
            'round_no',
            'game_period',
            'dwRound',
            'remark',
            'is_mark',
            'game_hall_code',
            'game_name',
            'cat_id',
            'ip_info',
            'game_result',
            'connect_mode'
        ];
        $db = UserChartInfo::select($field);
        switch ($this->agentInfo['grade_id']) {
            case 1:
                $db->where('hall_id',$this->agentId);
                break;
            case 2:
                $db->where('agent_id',$this->agentId);
                break;
        }

        if(isset($connect_mode) && $connect_mode !== '') {
            $connect_mode = (int)$connect_mode;
            if($connect_mode == 1) {
                $db->where('connect_mode',1);
//                $match['connect_mode'] = 1;
            } else {
                $db->where('connect_mode','<>', 1);
//                $match['connect_mode']['$ne'] = 1;
            }

        }

        if(isset($game_period) && !empty($game_period)) {
            $db->where('game_period',$game_period);
//            $match['game_period'] = $game_period;
        }

        //获取测试，联调代理id
        $ids = Agent::where(['grade_id' => 2, 'is_hall_sub' => 0])->whereIn('account_type',[2,3])->pluck('id');
        $db->whereNotIn('agent_id', $ids);

        if(isset($account) && !empty($account)) {
            $db->where('account',$account);
        }

        if(isset($user_id) && !empty($user_id)) {

            $db->where('user_id',(int)$user_id);
        }

        if(isset($game_hall_id) && $game_hall_id !== '') {

            $db->where('game_hall_id',(int)$game_hall_id);
        }

        if(isset($round_no) && !empty($round_no)) {

            $db->where('round_no',$round_no);
        }

        if(isset($status) && !empty($status)) {
            switch ($status) {
                //未取消未派彩
                case 1:
                    $db->where('is_cancel',0);
                    $db->where('is_mark',0);
                    break;
                //未取消已派彩
                case 2:
                    $db->where('is_cancel',0);
                    $db->where('is_mark',1);
                    break;
                //已取消未派彩
                case 3:
                    $db->where('is_cancel',1);
                    $db->where('is_mark',0);
                    break;
                //已取消已派彩
                case 4:
                    $db->where('is_cancel',1);
                    $db->where('is_mark',1);
                    break;
            }
        }

        if(isset($start_add_time) && !empty($start_add_time)) {
            $s_time = strtotime($start_add_time);
            $db->where('end_time', '>=', new \MongoDB\BSON\UTCDateTime($s_time * 1000));
        }

        if(isset($end_add_time) && !empty($end_add_time)) {
            $e_time = strtotime($end_add_time) + 1;
            $db->where('end_time', '<', new \MongoDB\BSON\UTCDateTime($e_time * 1000));
        }

        $db->orderby('start_time','desc');

        if($is_page) {
            $re = $db->paginate((int)$page_num);
        } else {
            $re = $db->get();
        }


        //此次由于mongodb保存的时间类型是isodate，返回一个对象，转时间日期时，需要转格式

        $total_score = [
            'total_bet_score' => 0,
            'valid_bet_score_total' => 0,
            'total_win_score' => 0,
        ];
        foreach ($re as &$v){
            $v['connect_mode'] = isset($v['connect_mode']) ? $v['connect_mode'] : 0;//添加扣费模式字段
            $v->start_time = $v->start_time->__tostring();
            $v->start_time = date('Y-m-d H:i:s',$v->start_time/1000);
            !$v->is_cancel && $total_score['total_bet_score'] += $v->total_bet_score;

            if( $v->end_time ) {
                $v->end_time = $v->end_time->__tostring();
                $v->end_time = date('Y-m-d H:i:s',$v->end_time/1000);
            }

            !$v->is_cancel && $total_score['valid_bet_score_total'] += $v->valid_bet_score_total;
            !$v->is_cancel && $total_score['total_win_score'] += $v->total_win_score;
            $v->total_bet_score = number_format($v->total_bet_score, 2);
            $v->valid_bet_score_total = number_format($v->valid_bet_score_total, 2);
            $v->total_win_score = number_format($v->total_win_score, 2);
            (time() - strtotime($v->start_time)) > 60 && $v->is_mark != 1 && $v->is_cancel != 1  ? $v->is_rollback = 1 : $v->is_rollback = 0; //判断注单是否异常(一分钟还未派彩则为异常数据)
        }

        unset($v);

        if( count($total_score) ) {
            $total_score['total_bet_score'] = number_format($total_score['total_bet_score'],2);
            $total_score['valid_bet_score_total'] = number_format($total_score['valid_bet_score_total'],2);
            $total_score['total_win_score'] = number_format($total_score['total_win_score'],2);
        }

        if( $is_page ) {
            $re = $re->toArray();
            $re['total_score'] = $total_score;


        } else {
            $re = ['data' => $re,'total_score'=>$total_score];
        }
        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>$re,
        ]);
    }

    //此接口已废弃
    public function showOrder($_id)
    {
        return '此接口已废弃';
        $playerOrder = PlayerOrder::select();

        $playerOrder->where('_id', $_id);
        switch ($this->agentInfo['grade_id']) {
            case 1:
                $playerOrder->where('hall_id',$this->agentId);
                break;
            case 2:
                $playerOrder->where('agent_id',$this->agentId);
                break;
        }

        $data = $playerOrder->first();

        if($data) {
            $data->add_time = date('Y-m-d H:i:s',$data->add_time->__tostring()/1000);
            $data->betarea_code = config('betarea.'.$data->cat_id.'.'.$data->bet_type)['betarea_code'];
            $data->bet_money = number_format($data->bet_money, 2);
            $data->bet_money_valid = number_format($data->bet_money_valid, 2);
            $data->payout_win = number_format($data->payout_win, 2);
            $data->odds = number_format($data->odds, 2);
        }
        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>$data,
        ]);
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/player/order/{account}/{round_no}",
     *   tags={"注单查询"},
     *   summary="查看注单详情结果",
     *   description="
     * 查看注单详情结果 account：玩家登录名，round_no:局ID
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': {
    'round_no': 'f20b7f4872e61148',//局id
    'server_name': '17',//桌号
    'add_time': '2017-07-05 01:52:49',//下注时间
    'remark': '23',//牌信息
    'cat_id': 2,//游戏分类ID
    'game_result': '23',//游戏结果
    'game_period': '876-104',//靴+局
    'total': {
    'bet_money': '1,800.00',//总下注金额
    'bet_money_valid': '1,800.00',//总有效下注金额
    'payout_win': '-1,800.00'//总派彩金额
    },
    'data': [
    {
    'odds': 36,//赔率
    'bet_money': '200.00',//下注金额
    'bet_money_valid': '200.00',//有效下注金额
    'payout_win': '-200.00',//派彩金额
    'bet_type': 157,//下注类型
    'cat_id': 2//游戏分类ID
    'game_hall_id': 2//游戏厅ID
    'game_id': 2//游戏ID
    }
    ]
    }
    }",
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="Accept",
     *     type="string",
     *     description="http头信息 *",
     *     required=true,
     *     default="Accept:application/vnd.agent.v1+json"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="token",
     *     type="string",
     *     description="token *",
     *     required=true,
     *     default="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vYWdlbnQudmEvYXBpL2F1dGhvcml6YXRpb24iLCJpYXQiOjE0OTEwMTEzNDEsImV4cCI6MTQ5MTIyNzM0MSwibmJmIjoxNDkxMDExMzQxLCJqdGkiOiJCdno0UzV5S3cyOVFpcTlmIiwic3ViIjoxfQ.--iIUXplgkrUJbigugamkK8f9HnwzSFuO7fehTDfVjQ"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="locale",
     *     type="string",
     *     description="语言",
     *     required=false,
     *     default="zh-cn"
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function showOrderDetail(string $account, string $round_no)
    {
        $bet_field = [
            'odds',
            'payout_win',
            'bet_money',
            'bet_money_valid',
            'bet_type',
            'cat_id',
            'game_hall_id',
            'game_id',
        ];
        $info_field = [
            'round_no',
            'server_name',
            'start_time',
            'game_result',
            'remark',
            'game_period',
            'cat_id',
        ];
        $info = UserChartInfo::select($info_field)->where('account', $account)->where('round_no', $round_no)->first();
        $info->add_time = date('Y-m-d H:i:s',$info->start_time->__tostring()/1000);
        unset($info->_id,$info->start_time);

        $datas = PlayerOrder::select($bet_field)->where('user_name', $account)->where('round_no', $round_no)->get();
        $total = [
            'bet_money' => 0,
            'bet_money_valid' => 0,
            'payout_win' => 0,
        ];

        foreach ($datas as $v){
            //统计金额
            $total['bet_money'] += $v->bet_money;
            $total['bet_money_valid'] += $v->bet_money_valid;
            $total['payout_win'] += $v->payout_win;

            //下注区域列表
            $v->bet_money = number_format($v->bet_money, 2);
            $v->bet_money_valid = number_format($v->bet_money_valid, 2);
            $v->payout_win = number_format($v->payout_win, 2);
            $v->odds = number_format($v->odds, 2);
            unset($v->_id);
        }

        $total['bet_money'] = number_format($total['bet_money'], 2);
        $total['bet_money_valid'] = number_format($total['bet_money_valid'], 2);
        $total['payout_win'] = number_format($total['payout_win'], 2);

        $info['data'] = $datas;
        $info['total'] = $total;

        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>$info,
        ]);
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/player/1761841/getUserBalance",
     *   tags={"玩家管理"},
     *   summary="查询玩家余额（共享钱包）",
     *   description="
     *  /player/{id}/getUserBalance ,{id}为玩家id
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': {
    'data': {
    'balance': 54172
    }
    }
    }",
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="Accept",
     *     type="string",
     *     description="http头信息 *",
     *     required=true,
     *     default="Accept:application/vnd.agent.v1+json"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="token",
     *     type="string",
     *     description="token *",
     *     required=true,
     *     default="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vYWdlbnQudmEvYXBpL2F1dGhvcml6YXRpb24iLCJpYXQiOjE0OTEwMTEzNDEsImV4cCI6MTQ5MTIyNzM0MSwibmJmIjoxNDkxMDExMzQxLCJqdGkiOiJCdno0UzV5S3cyOVFpcTlmIiwic3ViIjoxfQ.--iIUXplgkrUJbigugamkK8f9HnwzSFuO7fehTDfVjQ"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="locale",
     *     type="string",
     *     description="语言",
     *     required=false,
     *     default="zh-cn"
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function getUserBalance(Request $request,$user_id)
    {
        if(!$user_id)
        {
            return $this->response->array([
                'code'=>400,
                'text'=>trans('agent.param_error'),
                'result'=>'',
            ]);
        }

        //判断该用户所属的厅主是否有开通共享钱包
        $userInfo = DB::table("lb_user")->where(["uid"=>$user_id])->first();
        $hall_id = $userInfo->hall_id;
        $hallInfo = DB::table("lb_agent_user")->where(['id'=>$hall_id,'is_hall_sub'=>0,"grade_id"=>1])->first();
        $agentInfo = DB::table("lb_agent_user")->where(['id'=>$userInfo->agent_id,"grade_id"=>2])->first();

        if(!$hallInfo || !isset($hallInfo->connect_mode))
        {
            return $this->response->array([
                'code'=>400,
                'text'=>trans('agent.param_error'),
                'result'=>'',
            ]);
        }

        //如果用户所属的厅主不是共享钱包模式则直接返回用户的余额
        if($hallInfo->connect_mode != 1)
        {
            return $this->response->array([
                'code'=>0,
                'text'=>trans('agent.success'),
                'result'=>["data"=>[
                    "balance" => $userInfo->money
                ]],
            ]);
        }

        //如果用户所属的厅主为共享钱包模式则进调用包网获取用户余额
        $data["agent_id"] = $userInfo->agent_id;
        $data["agent_name"]  =  $userInfo->agent_name;
        $data["user_id"] = $userInfo->uid;
        $data["user_name"] = str_replace($agentInfo->agent_code,"",encrypt_($userInfo->user_name));
        $data["oper_code"] = 2;
        $server = new GameServerController();
        $res = $server->roundotUserBalanceMessage($data,1002);
        if($res)
        {
            return $this->response->array([
                'code'=>0,
                'text'=>trans('agent.success'),
                'result'=>["data"=>[
                    "balance" => $res['user_balance']
                ]],
            ]);
        }
        else
        {
            return $this->response->array([
                'code'=>400,
                'text'=>trans('agent.fails'),
                'result'=>'',
            ]);
        }

    }

}