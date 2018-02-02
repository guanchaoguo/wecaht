<?php
namespace App\Http\Controllers\V1;

use Carbon\Carbon;
use App\Models\Player;
use App\Models\Agent;
use Illuminate\Http\Request;

/**
 * Class HomeController
 * @package App\Http\Controllers\V1
 * @desc 首页统计
 */
class HomeController extends BaseController
{

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/statistics/today",
     *   tags={"首页统计"},
     *   summary="统计今日数据",
     *   description="
     *   成功返回字段说明
        {
        'code': 0,
        'text': '操作成功',
        'result': {
        'user_hour_active_amount': 0,//1小时活跃玩家数
        'add_user_num': 1,今日新增玩家数
        'user_online': 3,//当前在线人数
        'total_bet_score': 100,//今日投注额
        'total_win_score': 0,//今日派彩额
        'total_win': {//赢钱的代理（玩家）
        'name': 'csj_play',//代理（玩家）名称
        'money': 1000//赢钱的金额
        },
        'total_lose': {//输钱的代理（玩家）
        'name': '1aqiybAHYQZN',//代理（玩家）名称
        'money': -100//输钱的金额
        }
        }
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
    public function getTodayData()
    {
        $data = [
            'time' => date('Y-m-d H:i:s',time()),
            'user_hour_active_amount' =>self::getUser(1),//1小时活跃玩家数
            'add_user_num' =>self::getUser(2),//今日新增玩家数
            'user_online' =>self::getUser(3),//当前在线人数
            'total_bet_score' => 0,//今日派彩额
            'total_win_score' => 0,//今日派彩额
            'total_win' => [//今日赢钱最多的代理商（玩家）
                'name' => '',//代理（玩家）
                'money' => 0,//金额
            ],
            'total_lose' => [//今日输钱最多的代理商（玩家）
                'name' => '',//代理（玩家）
                'money' => 0,//金额
            ],
        ];

        $dt = Carbon::today();
        $year = $dt->year;
        $month = $dt->month;
        $day = $dt->day;

        $where = [
            'day_year' => $year,
            'day_month' => $month,
            'day_day' => $day,
        ];
        switch ($this->agentInfo['grade_id']) {
            //厅主
            case 1:
                //获取测试代理id
                $ids = Agent::where(['grade_id' => 2, 'is_hall_sub' => '0'])->whereIn('account_type',[2,3])->pluck('id');
                //今日派彩额、投注额
                $where['hall_id'] = $this->agentId;
                $statis_cash_agent = \DB::table('statis_cash_agent')->where($where)->whereNotIn('agent_id', $ids);
                $data['total_bet_score'] = number_format($statis_cash_agent->sum('total_bet_score'), 2);
                $data['total_win_score'] = number_format($statis_cash_agent->sum('total_win_score'), 2);
                //赢钱最多的代理
                $cash_agent_win = \DB::table('statis_cash_agent')->where($where)->where('operator_win_score','>',0)->whereNotIn('agent_id', $ids)->orderby('operator_win_score','desc')->first();
                if( $cash_agent_win) {
                    $data['total_win'] = [
                        'name' => $cash_agent_win->agent_name,//代理
                        'money' => number_format($cash_agent_win->operator_win_score,2),//金额
                    ];
                }

                //输钱最多的代理
                $cash_agent_lose = \DB::table('statis_cash_agent')->where($where)->where('operator_win_score','<',0)->whereNotIn('agent_id', $ids)->orderby('operator_win_score','asc')->first();
                if( $cash_agent_lose ) {
                    $data['total_lose'] = [
                        'name' => $cash_agent_lose->agent_name,//代理
                        'money' => number_format($cash_agent_lose->operator_win_score,2),//金额
                    ];
                }
                break;
            //代理
            case 2:

                $where['agent_id'] = $this->agentId;

                //今日派彩额、投注额
                $statis_cash_agent = \DB::table('statis_cash_agent')->where($where)->first();

                if($statis_cash_agent) {
                    $data['total_bet_score'] = number_format($statis_cash_agent->total_bet_score,2);
                    $data['total_win_score'] = number_format($statis_cash_agent->total_win_score,2);
                }
//                $statis_cash_player = \DB::table('statis_cash_player')->where($where);

                //赢钱最多的玩家
                $cash_user_win = \DB::table('statis_cash_player')->where($where)->where('total_win_score','>',0)->orderby('total_win_score','desc')->first();
                if( $cash_user_win ) {
                    $data['total_win'] = [
                        'name' => $cash_user_win->user_name,//玩家
                        'money' => number_format($cash_user_win->total_win_score,2),//金额
                    ];
                }
                //输钱最多的玩家
                $cash_user_lose = \DB::table('statis_cash_player')->where($where)->where('total_win_score','<',0)->orderby('total_win_score','asc')->first();
                if( $cash_user_lose ) {
                    $data['total_lose'] = [
                        'name' => $cash_user_lose->user_name,//玩家
                        'money' => number_format($cash_user_lose->total_win_score,2),//金额
                    ];
                }
                break;
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
     *   path="/statistics/today/moneyQuantity",
     *   tags={"首页统计"},
     *   summary="今日注单数、今日派彩总额、今日投注总额",
     *   description="
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': {
    'total_bet_count': '154',//今日注单数
    'total_bet_score': '420.00',//今日投注额
    'total_win_score': '1,530.00'//今日派彩额
    }
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
    public function getTodayMoneyQuantity()
    {
        $data = [
            'total_bet_count' => 0,//今日注单数
            'total_win_score' => 0,//今日派彩额
            'total_bet_score' => 0,//今日投注额
        ];

        $dt = Carbon::today();
        $year = $dt->year;
        $month = $dt->month;
        $day = $dt->day;

        $where = [
            'day_year' => $year,
            'day_month' => $month,
            'day_day' => $day,
        ];

        //今日派彩额、投注额、今日注单数
        $db = \DB::table('statis_cash_agent')->select(
            \DB::raw('SUM(total_bet_score) as total_bet_score'),
            \DB::raw('SUM(total_win_score) as total_win_score'),
            \DB::raw('SUM(total_bet_count) as total_bet_count')
        )->where($where);

        switch ($this->agentInfo['grade_id']) {

            //厅主身份
            case 1:
                //获取测试代理id
//                $ids = Agent::where(['grade_id' => 2, 'is_hall_sub' => '0'])->whereIn('account_type',[2,3])->pluck('id');
                $db->where('hall_id', $this->agentId)/*->whereNotIn('agent_id', $ids)*/;

                break;

            //代理商身份
            case 2:

                $db->where('agent_id', $this->agentId);

                break;

            default :

                return $this->response->array([
                    'code'=>0,
                    'text'=> trans('agent.success'),
                    'result'=>$data,
                ]);

        }

        $res = $db->first();
        $data['total_bet_score'] = number_format($res->total_bet_score, 2);
        $data['total_win_score'] = number_format($res->total_win_score, 2);
        $data['total_bet_count'] = number_format($res->total_bet_count);

        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>$data,
        ]);
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/statistics/today/user",
     *   tags={"首页统计"},
     *   summary="会员总数、一小时活跃玩家数、当前游戏玩家数、今日新增玩家数",
     *   description="
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': {
    'user_hour_active_amount': '154',//一小时活跃玩家数
    'user_total_num': '420',//会员总数
    'user_online': '1,530'//当前游戏玩家数
     'add_user_num':'100'//今日新增玩家
    }
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
    public function getTodayUser()
    {
        $data = [
            'user_hour_active_amount' => number_format( self::getUser(1) ),//1小时活跃玩家数
            'user_total_num' => number_format( self::getUser(4) ),//会员总数
            'user_online' => number_format( self::getUser(3) ),//当前在线人数
            'add_user_num' =>number_format( self::getUser(2) ),//今日新增玩家数
        ];

        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>$data,
        ]);
    }
    /**
     * 统计玩家相关信息
     * @param int $type 类型 1：1小时活跃玩家，2：今日新增玩家 ,3:当前在线人数，4：会员总数
     * @return int
     */
    private function getUser(int $type) : int
    {
        $player = Player::select('uid');
        switch ($this->agentInfo['grade_id']) {
            //厅主
            case 1:
                $player->where('hall_id', $this->agentId);
                break;
            //代理
            case 2:
                $player->where('agent_id', $this->agentId);
                break;
        }

        switch ($type) {
            //1小时活跃玩家
            case 1;
                $player->where('last_time', '<=', (new Carbon('-1 hour'))->toDateTimeString());
                $player->where('last_time', '>=', (new Carbon())->startOfDay()->toDateTimeString());
                $player->where('on_line', 'Y');

                break;
            //今日新增玩家
            case 2:
                $player->where('add_date', '>=', (new Carbon())->startOfDay()->toDateTimeString());
                $player->where('add_date', '<=', (new Carbon())->endOfDay()->toDateTimeString());
                break;
            //当前在线人数
            case 3:
                $player->where('on_line', 'Y');
//                $player->where('last_time', '>=', Carbon::now()->startOfDay()->toDateTimeString());
//                $player->where('last_time', '<=', Carbon::now()->endOfDay()->toDateTimeString());
                break;

            //会员总数
            case 4:

                break;
        }
        return $player->count();
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/statistics/today/agent/top10",
     *   tags={"首页统计"},
     *   summary="代理盈利排行",
     *   description="
     *   成功返回字段说明
        {
        'code': 0,
        'text': '操作成功',
        'result': {
        'data': [
        {
        'agent_name': 'h88888',
        'total_win_score': '358050.9984'
        },
        {
        'agent_name': 'ass123',
        'total_win_score': '0.0000'
        },
        {
        'agent_name': 'agent_test',
        'total_win_score': '-2269275.0000'
        }
        ]
        }
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
    public function getAgentWinScoreTop10()
    {

        if($this->agentInfo['grade_id'] == 2) {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.grade_id_error'),
                'result' => '',
            ]);
        }
        //今天的开始与当前时间
        $todayStartTime = Carbon::now()->startOfDay()->toDateTimeString();
        $todayEndTime = Carbon::now()->toDateTimeString();

        $db = \DB::table('statis_cash_agent')->select(
            'agent_name',
            \DB::raw('SUM(operator_win_score) as total_win_score')
        );
        $db->where('hall_id', $this->agentId);
        $db->where('add_date', '>=', $todayStartTime)->where('add_date', '<=', $todayEndTime);
        $db->groupby('agent_id')->orderby('operator_win_score', 'desc');
        $db->limit(10);
        $data = $db->get();
        $data = $data->each(function ($item) {
            $item->total_win_score = number_format( $item->total_win_score, 2);
        });
        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>[ 'data' => $data ]
        ]);
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/statistics/today/user/score/top10",
     *   tags={"首页统计"},
     *   summary="会员盈利排行",
     *   description="
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': {
    'data': [
    {
    'user_name': 'h82017liangyn',
    'total_win_score': '358050.9984'
    }
    ]
    }
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
    public function getUsertWinScoreTop10()
    {

        if($this->agentInfo['grade_id'] == 1) {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.grade_id_error'),
                'result' => '',
            ]);
        }
        //今天的开始与当前时间
        $todayStartTime = Carbon::now()->startOfDay()->toDateTimeString();
        $todayEndTime = Carbon::now()->toDateTimeString();

        $db = \DB::table('statis_cash_player')->select(
            'user_name',
            'total_win_score'
        );
        $db->where('agent_id', $this->agentId);
        $db->where('add_date', '>=', $todayStartTime)->where('add_date', '<=', $todayEndTime);
        $db->orderby('total_win_score', 'desc');
        $db->limit(10);
        $data = $db->get();
        $data = $data->each(function ($item) {
            $item->total_win_score = number_format( $item->total_win_score, 2);
        });
        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>[ 'data' => $data ]
        ]);
    }


    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/statistics/today/user/countScore/top10",
     *   tags={"首页统计"},
     *   summary="会员注单数数排名",
     *   description="
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': {
    'data': [
    {
    'user_name': 'h82017liangyn',
    'total_bet_count': 358050
    }
    ]
    }
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
    public function getUsertCountScoreTop10()
    {
        if($this->agentInfo['grade_id'] == 1) {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.grade_id_error'),
                'result' => '',
            ]);
        }
        //今天的开始与当前时间
        $todayStartTime = Carbon::now()->startOfDay()->toDateTimeString();
        $todayEndTime = Carbon::now()->toDateTimeString();

        $db = \DB::table('statis_cash_player')->select(
            'user_name',
            'total_bet_count'
        );
        $db->where('agent_id', $this->agentId);
        $db->where('add_date', '>=', $todayStartTime)->where('add_date', '<=', $todayEndTime);
        $db->orderby('total_bet_count', 'desc');
        $db->limit(10);
        $data = $db->get();
        $data = $data->each(function ($item) {
            $item->total_bet_count = number_format( $item->total_bet_count);
        });
        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>[ 'data' => $data ]
        ]);
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/statistics/today/activeUser/top10",
     *   tags={"首页统计"},
     *   summary="活跃会员数排名",
     *   description="
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': {
    'data': [
    {
    'agent_name': 'h88888',
    'active_user': '996'
    },
    {
    'agent_name': 'ass123',
    'active_user': '34'
    },
    {
    'agent_name': 'agent_test',
    'active_user': '3'
    }
    ]
    }
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
    public function getActiveUserTop10()
    {
        if($this->agentInfo['grade_id'] == 2) {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.grade_id_error'),
                'result' => '',
            ]);
        }

        //今天的开始与当前时间
        $todayStartTime = Carbon::now()->startOfDay()->toDateTimeString();
//        $todayStartTime = (new Carbon('-13 Day'))->startOfDay()->toDateTimeString();
//        var_export($todayStartTime);die;
        $todayEndTime = Carbon::now()->toDateTimeString();

        $db = \DB::table('statis_active_user')->select(
            'agent_name',
//            \DB::raw('SUM(active_user) as active_user')
            'active_user'
        );
        $db->where('hall_id', $this->agentId);
        $db->where('add_time', '>=', $todayStartTime)->where('add_time', '<=', $todayEndTime);
        $db->/*groupby('agent_id')->*/orderby('active_user', 'desc');
        $db->limit(10);
        $data = $db->get();
        $data = $data->each(function ($item) {
            $item->active_user = number_format( $item->active_user);
        });
        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>[ 'data' => $data ]
        ]);

    }


    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/statistics/week",
     *   tags={"首页统计"},
     *   summary="统计周数据（可不用，已分成下面两个接口）",
     *   description="
     *   代理商角色成功返回字段说明
        {
        'code': 0,
        'text': '操作成功',
        'result': {
        'Recharge_score': {
        'last_week': 0,
        'this_week': 2099
        },
        'add_player_num': {
        'last_week': 86,
        'this_week': 2
        },
        'total_bet_score': {
        'last_week': 0,
        'this_week': 561
        },
        'total_win_score': {
        'last_week': 0,
        'this_week': 550
        }
        }
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
    public function getWeekData()
    {
        //上周的开始时间
        $last_week_start_date = (new Carbon('-2 Sunday'))->toDateTimeString();
        //上周的结束时间
        $last_week_end_date = (new Carbon('last Sunday'))->toDateTimeString();
        //本周的开始时间
        $this_week_start_date = $last_week_end_date;
        //本周的结束时间
        $this_week_end_date = (new Carbon('this Sunday'))->toDateTimeString();
        $where = [];
        $agent = [];//新增代理
        $recharge = [];//充值
        $ids = [];
        switch ($this->agentInfo['grade_id']) {
            //厅主
            case 1:
                //获取测试代理id
                $ids = Agent::where([ 'grade_id' => 2, 'is_hall_sub' => '0'])->whereIn('account_type',[2,3])->pluck('id');
                $where['hall_id'] = $this->agentId;
                //上周添加代理数
                $last_week_add_agent = self::countAgents($last_week_start_date,$last_week_end_date);
                //本周添加代理数
                $this_week_add_agent = self::countAgents($this_week_start_date,$this_week_end_date);
                $agent = [
                    'add_agent_num' => [
                        'last_week' => (int)$last_week_add_agent,
                        'this_week' => (int)$this_week_add_agent,
                    ]
                ];
                break;
            //代理
            case 2:
                $where['agent_id'] = $this->agentId;
                //上周充值记录
                $last_week_score_record = \DB::table('statis_cash_agent')->where($where)->where('add_date','>=',$last_week_start_date)->where('add_date','<',$last_week_end_date)->sum('total_score_record');
                //本周充值记录
                $this_week_score_record = \DB::table('statis_cash_agent')->where($where)->where('add_date','>=',$this_week_start_date)->where('add_date','<',$this_week_end_date)->sum('total_score_record');

                $recharge = [
                    'Recharge_score' => [
                        'last_week' => (int)$last_week_score_record,
                        'this_week' => (int)$this_week_score_record,
                    ]
                ];
                break;
        }

        //上周总投注额
        $last_week_bet_score = \DB::table('statis_cash_agent')->where($where)->where('add_date','>=',$last_week_start_date)->where('add_date','<',$last_week_end_date)->whereNotIn('agent_id', $ids)->sum('total_bet_score');

        //上周总派彩额
        $last_week_win_score = \DB::table('statis_cash_agent')->where($where)->where('add_date','>=',$last_week_start_date)->where('add_date','<',$last_week_end_date)->whereNotIn('agent_id', $ids)->sum('total_win_score');

        //本周总投注额
        $this_week_bet_score = \DB::table('statis_cash_agent')->where($where)->where('add_date','>=',$this_week_start_date)->where('add_date','<',$this_week_end_date)->whereNotIn('agent_id', $ids)->sum('total_bet_score');

        //本周总派彩额
        $this_week_win_score = \DB::table('statis_cash_agent')->where($where)->where('add_date','>=',$this_week_start_date)->where('add_date','<',$this_week_end_date)->whereNotIn('agent_id', $ids)->sum('total_win_score');

        $data = [
            'add_player_num' => [
                'last_week' => self::countPlayers($last_week_start_date,$last_week_end_date),
                'this_week' => self::countPlayers($this_week_start_date,$this_week_end_date),
            ],
            'total_bet_score' => [
                'last_week' => round($last_week_bet_score,2),
                'this_week' => round($this_week_bet_score,2),
            ],
            'total_win_score' => [
                'last_week' => round($last_week_win_score,2),
                'this_week' => round($this_week_win_score,2),
            ],
        ];

        $data = array_merge($agent, $recharge, $data);
        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => $data,
        ]);
    }


    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/statistics/user",
     *   tags={"首页统计"},
     *   summary="统计周（月）用户量",
     *   description="
     *   代理商角色成功返回字段说明
        {
        'code': 0,
        'text': '操作成功',
        'result': [
        {
        'name': 'last_week\last_month',
        'type': 'bar',
        'data': [
        408//新增玩家
        ]
        },
        {
        'name': 'this_week\this_month',
        'type': 'bar',
        'data': [
        90//新增玩家
        ]
        }
        ]
        }
     *  厅主角色返回字段说明
        {
        'code': 0,
        'text': '操作成功',
        'result': [
        {
        'name': 'last_week\last_month',
        'type': 'bar',
        'data': [
        3,//新增代理
        408//新增玩家
        ]
        },
        {
        'name': 'this_week\this_month',
        'type': 'bar',
        'data': [
        2,//新增代理
        90//新增玩家
        ]
        }
        ]
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
     *     name="type",
     *     type="integer",
     *     description="类型：1周统计，2月统计",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function getDataByUser(Request $request)
    {
        $type = (int)$request->input('type');

        switch ($type){
            //按周统计
            case 1:
                //上周的开始时间
                $last_start_date = (new Carbon('-2 Sunday'))->toDateTimeString();
                //上周的结束时间
                $last_end_date = (new Carbon('last Sunday'))->toDateTimeString();
                //本周的开始时间
                $this_start_date = $last_end_date;
                //本周的结束时间
                $this_end_date = (new Carbon('this Sunday'))->toDateTimeString();
                $last_name = 'last_week';
                $this_name = 'this_week';
                break;
            //按月统计
            case 2:

                //上月的开始、结束时间
                $last_start_date = (new Carbon('last Month'))->startOfMonth()->toDateTimeString();
                $last_end_date = (new Carbon('last Month'))->endOfMonth()->toDateTimeString();
                //本月的开始、结束时间
                $this_start_date = (new Carbon('this Month'))->startOfMonth()->toDateTimeString();
                $this_end_date = (new Carbon('this Month'))->endOfMonth()->toDateTimeString();
                $last_name = 'last_month';
                $this_name = 'this_month';
                break;
            default:
                return $this->response->array([
                    'code'=> 400,
                    'text'=> trans('agent.param_error'),
                    'result'=>'',
                ]);
                break;
        }


        $where = [];
        $last_data = [];
        $this_data = [];
        switch ($this->agentInfo['grade_id']) {
            //厅主
            case 1:
                $where['hall_id'] = $this->agentId;
                //上周添加代理数
                $last_add_agent = self::countAgents($last_start_date,$last_end_date);
                //本周添加代理数
                $this_add_agent = self::countAgents($this_start_date,$this_end_date);

                $last_data[] = $last_add_agent;
                $this_data[] = $this_add_agent;
                break;
        }

        $last_player = self::countPlayers($last_start_date,$last_end_date);
        $this_player = self::countPlayers($this_start_date,$this_end_date);
        $last_data[] = $last_player;
        $this_data[] = $this_player;

        $data = [
            [
                'name' => $last_name,
                'type' => 'bar',
                'data' =>$last_data,
            ],
            [
                'name' => $this_name,
                'type' => 'bar',
                'data' =>$this_data,
            ],
        ];

        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => $data,
        ]);
    }


    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/statistics/score",
     *   tags={"首页统计"},
     *   summary="统计周（月）（金额）",
     *   description="
     *   代理商角色成功返回字段说明
        {
        'code': 0,
        'text': '操作成功',
        'result': [
        {
        'name': 'last_week\last_month',
        'type': 'bar',
        'data': [
        0,//充值
        0,//总投注
        0//总派彩
        ]
        },
        {
        'name': 'this_week\last_month',
        'type': 'bar',
        'data': [
        '99.0000',//充值
        '111.0000',//总投注
        '100.0000'//总派彩
        ]
        }
        ]
        }
     *   厅主角色成功返回字段说明
        {
        'code': 0,
        'text': '操作成功',
        'result': [
        {
        'name': 'last_week\last_month',
        'type': 'bar',
        'data': [
        3,//总投注额
        408//总派彩
        ]
        },
        {
        'name': 'this_week\this_month',
        'type': 'bar',
        'data': [
        2,//总投注额
        90//总派彩
        ]
        }
        ]
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
     *     name="type",
     *     type="integer",
     *     description="类型：1周统计，2月统计",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function getDataByScore(Request $request)
    {
        $type = (int)$request->input('type');

        switch ($type){
            //按周统计
            case 1:
                //上周的开始时间
                $last_start_date = (new Carbon('-2 Sunday'))->toDateTimeString();
                //上周的结束时间
                $last_end_date = (new Carbon('last Sunday'))->toDateTimeString();
                //本周的开始时间
                $this_start_date = $last_end_date;
                //本周的结束时间
                $this_end_date = (new Carbon('this Sunday'))->toDateTimeString();

                $last_name = 'last_week';
                $this_name = 'this_week';
                break;
            //按月统计
            case 2:

                //上月的开始、结束时间
                $last_start_date = (new Carbon('last Month'))->startOfMonth()->toDateTimeString();
                $last_end_date = (new Carbon('last Month'))->endOfMonth()->toDateTimeString();
                //本月的开始、结束时间
                $this_start_date = (new Carbon('this Month'))->startOfMonth()->toDateTimeString();
                $this_end_date = (new Carbon('this Month'))->endOfMonth()->toDateTimeString();
                $last_name = 'last_month';
                $this_name = 'this_month';
                break;
            default:
                return $this->response->array([
                    'code'=> 400,
                    'text'=> trans('agent.param_error'),
                    'result'=>'',
                ]);
                break;
        }

        $where = [];
        $last_data = [];
        $this_data = [];
        $ids = [];
        switch ($this->agentInfo['grade_id']) {
            //厅主
            case 1:
                //获取测试代理id
                $ids = Agent::where(['grade_id' => 2, 'is_hall_sub' => '0'])->whereIn('account_type',[2,3])->pluck('id');
                $where['hall_id'] = $this->agentId;
                break;
            //代理
            case 2:
                $where['agent_id'] = $this->agentId;
                //上周（月）充值记录
                $last_week_score_record = \DB::table('statis_cash_agent')->where($where)->where('add_date','>=',$last_start_date)->where('add_date','<',$last_end_date)->sum('total_score_record');
                //本周（月）充值记录
                $this_week_score_record = \DB::table('statis_cash_agent')->where($where)->where('add_date','>=',$this_start_date)->where('add_date','<',$this_end_date)->sum('total_score_record');

                $last_data[] = round($last_week_score_record,2);
                $this_data[] = round($this_week_score_record,2);

                break;
        }

        //上周（月）总投注额
        $last_bet_score = \DB::table('statis_cash_agent')->where($where)->where('add_date','>=',$last_start_date)->where('add_date','<',$last_end_date)->whereNotIn('agent_id', $ids)->sum('total_bet_score');

        //上周（月）总派彩额
        $last_win_score = \DB::table('statis_cash_agent')->where($where)->where('add_date','>=',$last_start_date)->where('add_date','<',$last_end_date)->whereNotIn('agent_id', $ids)->sum('total_win_score');

        //本周（月）总投注额
        $this_bet_score = \DB::table('statis_cash_agent')->where($where)->where('add_date','>=',$this_start_date)->where('add_date','<',$this_end_date)->whereNotIn('agent_id', $ids)->sum('total_bet_score');

        //本周（月）总派彩额
        $this_win_score = \DB::table('statis_cash_agent')->where($where)->where('add_date','>=',$this_start_date)->where('add_date','<',$this_end_date)->whereNotIn('agent_id', $ids)->sum('total_win_score');

        $last_data[] = round($last_bet_score,2);
        $last_data[] = round($last_win_score,2);

        $this_data[] = round($this_bet_score,2);
        $this_data[] = round($this_win_score,2);

        $data = [
            [
                'name' => $last_name,
                'type' => 'bar',
                'data' =>$last_data,
            ],
            [
                'name' => $this_name,
                'type' => 'bar',
                'data' =>$this_data,
            ],
        ];

        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => $data,
        ]);
    }



    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/statistics/semi-annual",
     *   tags={"首页统计"},
     *   summary="近半年投注额、派彩统计",
     *   description="
     *   成功返回字段说明
        {
        'code': 0,
        'text': '操作成功',
        'result': {
        'data': [
        '12月',
        '1月',
        '2月'
        ],
        'series': [
        {
        'name': 'T1002',
        'type': 'line',
        'data': [
        '23.0000',
        '20.0000',
        '20.0000'
        ]
        },
        {
        'name': 'T1001',
        'type': 'line',
        'data': [
        '232.0000',
        '352.0000',
        '110.0000'
        ]
        }
        ]
        }
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
     *     name="type",
     *     type="integer",
     *     description="类型：1周统计，2月统计",
     *     required=true,
     *     default="zh-cn"
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function getSemiAnnualData()
    {
        //近半年的开始、结束时间
        $month_start_date = (new Carbon('-5 Month'))->startOfMonth()->toDateTimeString();
//        $month_end_date = (new Carbon('last Month'))->endOfMonth()->toDateTimeString();
        $month_end_date = Carbon::now()->toDateTimeString();
        $where = [];
        $ids = [];
        switch ($this->agentInfo['grade_id']){
            case 1:
                //获取测试代理id
                $ids = Agent::where(['grade_id' => 2, 'is_hall_sub' => '0'])->whereIn('account_type',[2,3])->pluck('id');
                $where['hall_id'] = $this->agentId;
                break;
            case 2:
                $where['agent_id'] = $this->agentId;
                break;
        }
        //近半年总投注额、总派彩额
        $score = \DB::table('statis_cash_agent')->select(array(
            \DB::raw('day_month as month'),
            \DB::raw('DATE_FORMAT(add_date,"%Y-%m") as add_date'),
            \DB::raw('SUM(total_bet_score) as bet_score'),
            \DB::raw('SUM(total_win_score) as win_score')
        ))->where($where)->where('add_date','>=',$month_start_date)->where('add_date','<',$month_end_date)->whereNotIn('agent_id', $ids)->groupby('day_month')->orderby('day_year')->orderby('day_month')->get();

        //近6个月月份
        $month = [
            (new Carbon('-5 Month'))->format('Y-m'),
            (new Carbon('-4 Month'))->format('Y-m'),
            (new Carbon('-3 Month'))->format('Y-m'),
            (new Carbon('-2 Month'))->format('Y-m'),
            (new Carbon('-1 Month'))->format('Y-m'),
            (new Carbon('this Month'))->format('Y-m'),
        ];
        //总投注初始化
        $bet_score = [0,0,0,0,0,0];
        //总派彩初始化
        $win_score = [0,0,0,0,0,0];
        if($score) {
            for ($i = 0; $i < 6; $i++) {
                foreach ($score as $v) {
                    if($month[$i] == $v->add_date) {
                        $bet_score[$i] = round($v->bet_score,2);
                        $win_score[$i] = round($v->win_score,2);
                        break;
                    }
                }
            }
        }

        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => [
                'data' => $month,
                'series' => [
                    [
                        'name' => 'T1002',
                        'type' => 'line',
                        'data' => $win_score,
                    ],
                    [
                        'name' => 'T1001',
                        'type' => 'line',
                        'data' => $bet_score,
                    ],
                ],
            ],
        ]);
    }

    /**
     * 代理统计
     * @param string $start_date
     * @param string $end_date
     * @return int
     */
    private  function countAgents(string $start_date, string $end_date) : int
    {
        $where = ['grade_id' => 2,'is_hall_sub'=>0,'parent_id'=>$this->agentId];

        return Agent::where($where)->where('add_time','>=',$start_date)->where('add_time','<',$end_date)->whereNotIn('account_type',[2,3])->count();
    }

    /**
     * 玩家统计
     * @param string $start_date
     * @param string $end_date
     * @return int
     */
    private  function countPlayers(string $start_date, string $end_date) : int
    {
        $where = [];
        switch ($this->agentInfo['grade_id']) {
            //厅主
            case 1:
                $where['hall_id'] = $this->agentId;
                break;
            //代理
            case 2:
                $where['agent_id'] = $this->agentId;
                break;
        }

        return Player::where($where)->where('user_rank','=','0')->where('add_date','>=',$start_date)->where('add_date','<',$end_date)->count();
    }
}