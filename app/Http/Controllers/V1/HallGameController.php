<?php
/**
 * 游戏管理控制器
 * User: chensongjian
 * Date: 2017/4/6
 * Time: 9:28
 */

namespace App\Http\Controllers\V1;

use App\Models\GameInfo;
use Illuminate\Http\Request;
use App\Models\Agent;
use App\Models\AgentGame;
use App\Models\GameHall;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

class HallGameController extends BaseController
{


    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/hall/game",
     *   tags={"游戏管理"},
     *   summary="游戏厅游戏分组列表",
     *   description="
     *   成功返回字段说明
        {
        'code': 0,
        'text': '操作成功',
        'result': [
        {
        'game_hall_id': 0,//游戏厅id
        'game_hall_code': 'GH0001',//游戏厅标识码
        'games': [//游戏数组
        {
        'game_id': 91,//游戏id
        'game_name': '百家乐',//游戏名称
        'game_sta': 1,//游戏状态：1为可用,0为不可用，2已删除
        'status': 0//是否显示： 1显示，0不显示
        }
        ]
        }
        ]
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
     *     default=""
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
    public function index(Request $request)
    {

        if($this->agentInfo['grade_id'] == 2) {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.grade_id_error'),
                'result' => '',
            ]);
        }

        $hallGames = Agent::find($this->agentId)->hallGames;
        $games = [];
        foreach ($hallGames as $key => $v)
        {
            $games[$v['hall_id']]['game_hall_id'] = $v['hall_id'];
            $games[$v['hall_id']]['game_hall_code'] = $v['game_hall_code'];

            $games[$v['hall_id']]['games'][] = ['game_id'=>$v['game_id'],'game_name'=>$v['game_name'],'game_sta'=>$v['game_sta'],'status'=>$v['status']];
        }

        return $this->response->array([
            'code' => 0,
            'text' =>trans('agent.success'),
            'result' => array_values($games),
        ]);
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Patch(
     *   path="/hall/game/{game_id}/status",
     *   tags={"游戏管理"},
     *   summary="修改游戏状态（1显示，0不显示）",
     *   description="
     *   {game_id} = 游戏id
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
     *     default=""
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
     *     name="hall_id",
     *     type="integer",
     *     description="厅id",
     *     required=true,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="status",
     *     type="integer",
     *     description="是否显示 1：显示，0：不显示",
     *     required=true,
     *     default="0"
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function statusUpdate(Request $request,  int $game_id)
    {
        if($this->agentInfo['grade_id'] == 2) {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.grade_id_error'),
                'result' => '',
            ]);
        }

        $status = $request->input('status');
        $hall_id = $request->input('hall_id');
        if( !in_array($status,[0, 1]) ) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.param_error'),
                'result' => '',
            ]);
        }

        $where = [
            'agent_id' => $this->agentId,
            'game_id' => $game_id,
            'hall_id' => $hall_id,
        ];
        $re = AgentGame::where($where)->update(['status' => $status]);
        if($re === false) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.save_fails'),
                'result' => '',
            ]);
        }

        // 添加操作日志
        $hall = ['旗舰厅', '贵宾厅','金臂厅' ,'至尊厅'];
        $hallName =  $hall[$hall_id];
        $stat =  $status == 1 ? '显示':'不显示' ; //1 显示，0 不显示
        @addLog([
            'action_name'=>'修改游戏状态',
            'action_desc'=> $stat."游戏; 修改厅： $hallName 游戏ID：{$game_id}",
            'action_passivity'=>'用户权限组'
        ]);

        return $this->response->array([
            'code' => 0,
            'text' =>trans('agent.success'),
            'result' => '',
        ]);
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Put(
     *   path="/hall/game/status",
     *   tags={"游戏管理"},
     *   summary="保存游戏显示状态（批量）",
     *   description="
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
     *     default=""
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
     *     name="items",
     *     type="string",
     *     description="保存状态为1的数组格式：['game_hall_id-game_id']",
     *     required=true,
     *     default=""
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function batchUpdateStatus(Request $request)
    {
        if($this->agentInfo['grade_id'] == 2) {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.grade_id_error'),
                'result' => '',
            ]);
        }

        $items = $request->input('items');
        /*if( ! $items ) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.param_error'),
                'result' => '',
            ]);
        }*/
        $agent_games = AgentGame::where('agent_id', $this->agentId)->get()->toArray();
        $insert_data = [];

        if($items) {
            foreach ($agent_games as $agent_game){
                foreach ($items as $v) {
                    $tmp = explode('-', $v);
                    if($agent_game['hall_id'] == $tmp[0] && $agent_game['game_id'] == $tmp[1]) {
                        $agent_game['status'] = 1;
                        break;
                    } else {
                        $agent_game['status'] = 0;
                    }
                }
                $insert_data[] = $agent_game;
            }
            \DB::beginTransaction();
            AgentGame::where('agent_id', $this->agentId)->delete();
            $res = AgentGame::insert($insert_data);
        } else {
            $res = AgentGame::where('agent_id', $this->agentId)->update(['status' => 0]);
            $res = $res !== false ? 1 : 0;
        }



        if($res) {
            \DB::commit();
            @addLog([
                'action_name'=>'保存游戏状态',
                'action_desc'=> "批量保存游戏显示状态",
                'action_passivity'=>'游戏管理'
            ]);

            // 添加厅主缓存
            self::setHallGame($this->agentId);

            return $this->response->array([
                'code' => 0,
                'text' =>trans('agent.success'),
                'result' => '',
            ]);
        }

        \DB::rollBack();
        return $this->response->array([
            'code' => 400,
            'text' =>trans('agent.save_fails'),
            'result' => '',
        ]);
    }
    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/gameHall",
     *   tags={"游戏管理"},
     *   summary="游戏厅",
     *   description="
     *   成功返回字段说明
    {
    'code': 0,
    'text': '保存成功',
    'result': [{
        'id': 0,
        'title': '旗舰厅',
        'game_hall_code': 'GH0001',
        'desc': '旗舰厅'
    }]
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
     *     default=""
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
    public function gameHall(Request $request)
    {
        $data = GameHall::select('*')->orderBy('id')->get();
        return $this->response->array([
            'code' => 0,
            'text' =>trans('agent.success'),
            'result' => $data,
        ]);
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/game",
     *   tags={"游戏管理"},
     *   summary="游戏选择列表",
     *   description="
     *   成功返回字段说明
        {
        'code': 0,
        'text': '保存成功',
        'result': [{
            'id': 91,
            'game_name': '百家乐'
        }]
        }",
     *   operationId="games",
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
     *     default=""
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
    public function games(Request $request)
    {
        $where = [];
        switch ($this->agentInfo['grade_id']){
            case 1:

                $where['agent_id'] = $this->agentInfo['id'];
                break;
            case 2:

                $where['agent_id'] = $this->agentInfo['parent_id'];
                break;
        }

        $ids = AgentGame::where($where)->pluck('game_id')->unique();
        $data = GameInfo::select('id','game_name')->whereIn('id',$ids)->get();

        return $this->response->array([
            'code' => 0,
            'text' =>trans('agent.success'),
            'result' => $data,
        ]);
    }

    /**
     * @param $hall_name
     */
    public static  function setHallGame($agent_id){
        $keyName = 'agent_game:'. (int)$agent_id;
        // 查询当前的厅主开通的游戏
        $agentGameInfo = DB::table('agent_game')->join('game_info','agent_game.game_id','=','game_info.id')->select(['agent_game.game_id','agent_game.hall_id'])->where('agent_game.agent_id', $agent_id)->where('agent_game.status',1)->where('game_info.status',1)->get();
        $redis = Redis::connection("default");
        $redis->del($keyName);
        if( count($agentGameInfo) ) {
            $data = [];
            foreach (StringShiftToInt($agentGameInfo,['game_id','hall_id']) as $item){
                $data[] = json_encode($item);
            }
            $redis->rpush($keyName, $data);
        }
    }
}