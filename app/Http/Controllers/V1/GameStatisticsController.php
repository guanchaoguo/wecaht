<?php
/**
 * Created by PhpStorm.
 * User: chengkang
 * Date: 2017/2/8
 * Time: 17:38
 */
namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Models\UserChartInfo;
use App\Models\Agent;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;

class GameStatisticsController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
        if( ! File::exists('excel/')) {
            File::makeDirectory('excel/');
        }
    }
    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/totalBet",
     *   tags={"报表统计"},
     *   summary="查询总投注额",
     *   description="
     *   厅主角色：成功返回字段说明
        {
        'code': 0,
        'text': '操作成功',
        'result': {
        'total': 1,//总页数
        'per_page': '10',//每页显示条数
        'current_page': '1',//当前页
        'data': [
        {
        'agent_id': 2,//代理id
        'agent_name': 'agent_test',//代理名称
        'game_hall_id': 0,//厅id
        'game_hall_code': 'GH0001',//厅标识码
        'game_round_num': 23,//总笔数
        'valid_bet_score_total': 6450,//总有效投注额
        'total_bet_score': 7500,//总投注额
        'operator_win_score': 350//商家盈利
        }
        ],
        'total_page_score': {//当前页的小计
        'game_round_num': 23,//总笔数
        'valid_bet_score_total': 6450,//总有效投注额
        'total_bet_score': 7500,//总投注额
        'operator_win_score': 350//商家盈利
        },
        'total_score': {
        'game_round_num': 23,//总笔数
        'valid_bet_score_total': 6450,//总有效投注额
        'total_bet_score': 7500,//总投注额
        'operator_win_score': 350//商家盈利
        }
        }
        }
     *代理角色：成功返回字段说明
        {
        'code': 0,
        'text': '操作成功',
        'result': {
        'total': 1,//总页数
        'per_page': '10',//每页显示条数
        'current_page': '1',//当前页
        'data': [
        {
        'user_id': 965,//玩家id
        'account': 'a9TEST607821',//玩家账号
        'game_hall_id': 0,//厅id
        'game_hall_code': 'GH0001',//厅标识码
        'game_round_num': 23,//总笔数
        'valid_bet_score_total': 6450,//总有效投注额
        'total_bet_score': 7500,//总投注额
        'operator_win_score': 350//玩家盈利
        }
        ],
        'total_page_score': {//当前页的小计
        'game_round_num': 23,//总笔数
        'valid_bet_score_total': 6450,//总有效投注额
        'total_bet_score': 7500,//总投注额
        'operator_win_score': 350//玩家盈利
        },
        'total_score': {
        'game_round_num': 23,//总笔数
        'valid_bet_score_total': 6450,//总有效投注额
        'total_bet_score': 7500,//总投注额
        'operator_win_score': 350//玩家盈利
        }
        }
        }
     *  导出返回格式：
        {
        'code': 0,
        'text': '操作成功',
        'result': {
        'url': 'http://agent.va/excel/查询指定游戏_20170413.xlsx'
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
     *     name="game_hall_id",
     *     type="integer",
     *     description="游戏厅类型,0:旗舰厅，1贵宾厅，2：金臂厅， 3：至尊厅",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="login_type",
     *     type="integer",
     *     description="登录类型,0 网页登陆；1 手机字符登录 2 手机手势登录",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="start_time",
     *     type="string",
     *     description="开始时间 2017-01-20 15:07:07",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="end_time",
     *     type="string",
     *     description="结束时间  2017-01-20 15:07:07",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="page",
     *     type="integer",
     *     description="当前页",
     *     required=false,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="page_num",
     *     type="integer",
     *     description="每页显示条数",
     *     required=false,
     *     default="10"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="is_export",
     *     type="integer",
     *     description="是否导出",
     *     required=false,
     *     default="0"
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function totalBet(Request $request)
    {
        $game_hall_id = $request->input('game_hall_id');
        $login_type = $request->input('login_type');
        $start_time = $request->input('start_time');
        $end_time = $request->input('end_time');
        $is_export = $request->input('is_export',0);
        $page = $request->input('page',1);
        $page_num = $request->input('page_num',10);

        $skip = (int) ($page-1) * $page_num;
        $limit = (int) $page_num;

        //分组
        $group = [];
        //过滤
        $match = [
            'is_cancel'=>0,
        ];



        $p_where = [];
        switch ($this->agentInfo['grade_id']) {
            //厅主
            case 1:
                $group['agent_id'] = '$agent_id';//代理分组
                $sort = ['agent_id' => 1];
                $match['hall_id'] = $this->agentId;//过滤厅主
                $user_id = 'agent_id';
                $user_name = 'agent_name';
                $win_score = '$operator_win_score';
                break;
            //代理商
            case 2:
                $group['user_id'] = '$user_id';//玩家分组
                $sort = ['user_id' => 1];
                $match['agent_id'] = $this->agentId;//过滤代理
                $user_id = 'user_id';
                $user_name = 'account';
                $win_score = '$total_win_score';
                break;
        }

        //显示的字段
        $field = [
            $user_id => 1,
            $user_name => 1,
            'game_hall_id' =>1,
            'game_hall_code' =>1,
            'game_round_num' =>1,
            'valid_bet_score_total' =>1,
            'total_bet_score' =>1,
            'operator_win_score' =>$win_score,
//            'total_profit_score' => ['$subtract' => ['$valid_bet_score_total','$total_win_score']]
        ];



        //查询厅
        if(isset($game_hall_id) && $game_hall_id !== '') {
            $match['game_hall_id'] = (int)$game_hall_id;
        }

        //过滤登录类型
        if(isset($login_type) && $login_type !== '') {
            $match['login_type'] = (int)$login_type;
        }

        if(isset($start_time) && !empty($start_time)) {
            $s_time = new \MongoDB\BSON\UTCDateTime(strtotime($start_time)* 1000);
            $match['start_time']['$gte'] = $s_time;
        }

        if(isset($end_time) && !empty($end_time)) {
            $e_time = new \MongoDB\BSON\UTCDateTime(strtotime($end_time)* 1000);
            $match['start_time']['$lt'] = $e_time;
        }


        //数据导出
        if($is_export) {
            set_time_limit(0);
            ini_set('memory_limit','500M');
            $data = $count_data = self::getUserChartInfo($group, $match, $field,$sort, 0, 1000);
            //金额格式化
            foreach ($data as &$v) {
                if( $this->agentInfo['grade_id'] == 1 ) {
                    $obj = Agent::select('real_name')->find($v['agent_id']);
                    $v['agent_name'] = $v['agent_name']."（{$obj->real_name}）";
                }

                $v['valid_bet_score_total'] = number_format($v['valid_bet_score_total'],2);
                $v['total_bet_score'] = number_format($v['total_bet_score'],2);
                $v['operator_win_score'] = number_format($v['operator_win_score'],2);
                unset($v['agent_id'],$v['user_id']);
                unset($v['game_hall_id']);
                unset($v['game_hall_code']);
            }
            unset($v);

            $title = [
//                 $this->agentInfo['grade_id'] == 1 ? '代理ID' : '玩家id',
                trans('export.login_name'),
                trans('export.cout_num'),
                trans('export.valid_bet_score'),
                trans('export.bet_score'),
                trans('export.win_score'),
            ];

            $total_data = self::getCountScore($count_data);
            $num = count($title)-count($total_data);
            $total_arr = array_merge(array_fill(0, $num, ""), $total_data);
            $total_arr[0] = 'Total';
            array_push($data, $total_arr);

            array_unshift($data, $title);
            $sub_title = trans('export.sub_title');
            $widths = [15,10,12,12,12];
            //游戏厅标题
            $game_hall_title = trans('gamehall.title').':';
            $game_hall_title .= isset($game_hall_id) && $game_hall_id !== '' ? trans('gamehall.'.$game_hall_id) : trans('gamehall.all');
            $date_area = trans('export.date_range').':'.$start_time.' - '.$end_time;
            $header = $game_hall_title.','.$date_area;
            $filename = $sub_title.'_'.date('Ymd',time()).time();

            $re = self::export($filename,$sub_title,$header,$data,$widths);

            return $this->response->array([
                'code' => 0,
                'text' => trans('agent.success'),
                'result' => [
                    'url' => 'http://'.$request->server("HTTP_HOST").'/'.$re['full']
                ],
            ]);

        } else {
            $total_data = self::getUserChartInfo($group, $match, $field,$sort);
            $data  = $count_data = array_slice($total_data,$skip,$limit);

            foreach ($data as &$v) {
                if( $this->agentInfo['grade_id'] == 1 ) {
                    $obj = Agent::select('real_name')->find($v['agent_id']);
                    $v['agent_name'] = $v['agent_name']."（{$obj->real_name}）";
                }
                $v['valid_bet_score_total'] = number_format($v['valid_bet_score_total'],2);
                $v['total_bet_score'] = number_format($v['total_bet_score'],2);
                $v['operator_win_score'] = number_format($v['operator_win_score'],2);
            }
            unset($v);
            return $this->response->array([
                'code' => 0,
                'text' => trans('agent.success'),
                'result' => [
                    'total' => count($total_data),
                    'per_page' => $page_num,
                    'current_page' => $page,
                    'data' => $data,
                    'total_page_score' => self::getCountScore($count_data),
                    'total_score' => self::getCountScore($total_data),
                ],
            ]);

        }


    }

    private static function getCountScore($data)
    {
        $total = [
            'game_round_num' => 0,
            'valid_bet_score_total' => 0,
            'total_bet_score' => 0,
            'operator_win_score' => 0,
        ];
        if( $data ) {

            foreach ($data as $k => $v) {
                $total['game_round_num'] += $v['game_round_num'];
                $total['valid_bet_score_total'] += $v['valid_bet_score_total'];
                $total['total_bet_score'] += $v['total_bet_score'];
                $total['operator_win_score'] += $v['operator_win_score'];
            }

            $total['valid_bet_score_total'] = number_format($total['valid_bet_score_total'], 2);
            $total['total_bet_score'] = number_format($total['total_bet_score'], 2);
            $total['operator_win_score'] = number_format($total['operator_win_score'], 2);
        }

        return $total;
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/totalBet/agent",
     *   tags={"报表统计"},
     *   summary="查询指定代理",
     *   description="
     *   成功返回字段说明
        {
        'code': 0,
        'text': '操作成功',
        'result': {
        'data': [
        {
        'agent_id': 2,//agent_id:代理id
        'agent_name': 'agent_test',//agent_name:代理名称
        'game_hall_id': 3,//游戏厅 id
        'game_hall_code': 'GH0004',//游戏厅标识码
        'game_name': '骰宝 ',//游戏名称
        'game_round_num': 16,//局数
        'valid_bet_score_total': 3240,//有效投注额
        'total_bet_score': 4290,//投注额
        'operator_win_score': 100//商家或玩家盈利
        }
        ]
        }
        }
        导出返回格式：
        {
        'code': 0,
        'text': '操作成功',
        'result': {
        'url': 'http://agent.va/excel/查询指定游戏_20170413.xlsx'
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
     *     name="game_hall_id",
     *     type="integer",
     *     description="游戏厅类型,0:旗舰厅，1贵宾厅，2：金臂厅， 3：至尊厅",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="agent_id",
     *     type="integer",
     *     description="代理id",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="agent_name",
     *     type="string",
     *     description="代理名称",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="game_id",
     *     type="integer",
     *     description="游戏id",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="is_export",
     *     type="integer",
     *     description="是否导出",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="start_time",
     *     type="string",
     *     description="开始时间 2017-01-20 15:07:07",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="end_time",
     *     type="string",
     *     description="结束时间  2017-01-20 15:07:07",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function agentTotalBet(Request $request)
    {

        if($this->agentInfo['grade_id'] == 2) {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.grade_id_error'),
                'result' => '',
            ]);

        }
        $agent_id = $request->input('agent_id');
        $agent_name = $request->input('agent_name');
        $game_hall_id = $request->input('game_hall_id');
        $game_id = $request->input('game_id');
        $start_time = $request->input('start_time');
        $end_time = $request->input('end_time');
        $is_export = $request->input('is_export',0);

        if(!$agent_id && !$agent_name) {
            if($is_export) {
                return $this->response->array([
                    'code'=>400,
                    'text'=> trans('agent.no_data_export'),
                    'result'=>'',
                ]);
            }
            //要指定代理
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.agent_requiset'),
                'result'=>'',
            ]);
        }

        //按代理分组
        $group = [
            'agent_id' => '$agent_id',
        ];
        //显示的字段
        $field = [
            'agent_id' =>1,
            'agent_name' =>1,
            'game_hall_id' =>1,
            'game_hall_code' =>1,
            'game_name' =>1,
            'game_round_num' =>1,
            'valid_bet_score_total' =>1,
            'total_bet_score' =>1,
            'operator_win_score' =>1
        ];
        //过滤
        $match = [
            'is_cancel'=>0,
            'hall_id' => $this->agentId
        ];
        //查询厅
        if(isset($game_hall_id) && $game_hall_id !== '') {
            $match['game_hall_id'] = (int)$game_hall_id;
        }
        //代理id
        if(isset($agent_id) && !empty($agent_id)) {
            $match['agent_id'] = (int)$agent_id;
        }

        //代理名称 模糊搜索
        if(isset($agent_name) && !empty($agent_name)) {
//            $match['agent_name']['$regex'] = $agent_name;
            $match['agent_name'] = $agent_name;
        }

        if(isset($game_id) && !empty($game_id)) {
            $match['game_id'] = (int)$game_id;
        }

        if(isset($start_time) && !empty($start_time)) {
            $s_time = new \MongoDB\BSON\UTCDateTime(strtotime($start_time)* 1000);
            $match['start_time']['$gte'] = $s_time;
        }

        if(isset($end_time) && !empty($end_time)) {
            $e_time = new \MongoDB\BSON\UTCDateTime(strtotime($end_time)* 1000);
            $match['start_time']['$lt'] = $e_time;
        }

        $data = self::getUserChartInfo($group,$match,$field,['agent_id'=>1]);
        foreach ($data as &$v) {
            if($game_hall_id === '') {
                $v['game_hall_id'] = '';
                $v['game_hall_code'] = '';
            }
            if(!$game_id) {
                $v['game_name'] = '';
            }

            $obj = Agent::select('real_name')->find($v['agent_id']);
            $v['agent_name'] = $v['agent_name']."（{$obj->real_name}）";
            $v['valid_bet_score_total'] = number_format($v['valid_bet_score_total'], 2);
            $v['total_bet_score'] = number_format($v['total_bet_score'], 2);
            $v['operator_win_score'] = number_format($v['operator_win_score'], 2);
            if($is_export) {
                $v['game_hall_id'] = $v['game_hall_id'] ? trans('gamehall.'.$v['game_hall_id']) : trans('gamehall.all');
                $v['game_name'] = $v['game_name'] ? $v['game_name'] : trans('gamehall.all');
                unset($v['agent_id']);
                unset($v['game_hall_code']);
            }
        }

        //数据导出
        if($is_export) {
            set_time_limit(0);
            ini_set('memory_limit','500M');
            $title = [
//                '代理ID',
                trans('export.login_name'),
                trans('export.game_hall_title'),
                trans('export.game_title'),
                trans('export.cout_num'),
                trans('export.valid_bet_score'),
                trans('export.bet_score'),
                trans('export.win_score'),
            ];
            array_unshift($data, $title);
            $sub_title = trans('export.sub_agent_title');
            $widths = [15,12,13,12,10,10,10];
            $header = trans('export.date_range').':'.$start_time.' - '.$end_time;
            $filename = $sub_title.'_'.date('Ymd',time()).time();

            $re = self::export($filename,$sub_title,$header,$data,$widths);

            return $this->response->array([
                'code' => 0,
                'text' => trans('agent.success'),
                'result' => [
                    'url' => 'http://'.$request->server("HTTP_HOST").'/'.$re['full']
                ],
            ]);

        } else {
            return $this->response->array([
                'code' => 0,
                'text' => trans('agent.success'),
                'result' => [
                    'data' => $data,
                ],
            ]);
        }

    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/totalBet/player",
     *   tags={"报表统计"},
     *   summary="查询指定玩家",
     *   description="
     *   成功返回字段说明
        {
        'code': 0,
        'text': '操作成功',
        'result': {
        'data': [
        {
        'user_id': 2,//玩家id
        'account': 'agent_test',//玩家账号
        'game_hall_id': 3,//游戏厅 id
        'game_hall_code': 'GH0004',//游戏厅标识码
        'game_name': '骰宝 ',//游戏名称
        'game_round_num': 16,//局数
        'valid_bet_score_total': 3240,//有效投注额
        'total_bet_score': 4290,//投注额
        'total_win_score': 100//玩家盈利
        }
        ]
        }
        }
        导出返回格式：
        {
        'code': 0,
        'text': '操作成功',
        'result': {
        'url': 'http://agent.va/excel/查询指定游戏_20170413.xlsx'
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
     *     name="game_hall_id",
     *     type="integer",
     *     description="游戏厅类型,0:旗舰厅，1贵宾厅，2：金臂厅， 3：至尊厅",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="user_id",
     *     type="integer",
     *     description="玩家id",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="account",
     *     type="string",
     *     description="玩家登录账号",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="game_id",
     *     type="integer",
     *     description="游戏id",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="is_export",
     *     type="integer",
     *     description="是否导出",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="start_time",
     *     type="string",
     *     description="开始时间 2017-01-20 15:07:07",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="end_time",
     *     type="string",
     *     description="结束时间  2017-01-20 15:07:07",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function playerTotalBet(Request $request)
    {

        /*if($this->agentInfo['grade_id'] == 1) {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.grade_id_error'),
                'result' => '',
            ]);

        }*/

        $user_id = $request->input('user_id');
        $account = $request->input('account');
        $game_hall_id = $request->input('game_hall_id');
        $game_id = $request->input('game_id');
        $start_time = $request->input('start_time');
        $end_time = $request->input('end_time');
        $is_export = $request->input('is_export',0);

        if(!$user_id && !$account) {
            if($is_export) {
                return $this->response->array([
                    'code'=>400,
                    'text'=> trans('agent.no_data_export'),
                    'result'=>'',
                ]);
            }
            //要指定玩家
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.player_requiset'),
                'result'=>'',
            ]);
        }

        //按玩家分组
        $group = [
            'user_id' => '$user_id',
        ];
        //显示的字段
        $field = [
//            'user_id' =>1,
            'account' =>1,
            'game_hall_id' =>1,
            'game_hall_code' =>1,
            'game_name' =>1,
            'game_round_num' =>1,
            'valid_bet_score_total' =>1,
            'total_bet_score' =>1,
            'total_win_score' =>1,
        ];
        //过滤
        $match = [
            'is_cancel'=>0,
        ];

        switch ($this->agentInfo['grade_id']) {
            //厅主
            case 1:
                $match['hall_id'] = $this->agentId;//过滤厅主
                break;
            //代理商
            case 2:
                $match['agent_id'] = $this->agentId;//过滤代理
                break;
        }
        //查询厅
        if(isset($game_hall_id) && $game_hall_id !== '') {
            $match['game_hall_id'] = (int)$game_hall_id;
        }
        //玩家id
        if(isset($user_id) && !empty($user_id)) {
            $match['user_id'] = (int)$user_id;
        }

        //玩家名称
        if(isset($account) && !empty($account)) {
//            $match['account']['$regex'] = $account;
            $match['account'] = $account;
        }

        if(isset($game_id) && !empty($game_id)) {
            $match['game_id'] = (int)$game_id;
        }

        if(isset($start_time) && !empty($start_time)) {
            $s_time = new \MongoDB\BSON\UTCDateTime(strtotime($start_time)* 1000);
            $match['start_time']['$gte'] = $s_time;
        }

        if(isset($end_time) && !empty($end_time)) {
            $e_time = new \MongoDB\BSON\UTCDateTime(strtotime($end_time)* 1000);
            $match['start_time']['$lt'] = $e_time;
        }
        $data = self::getUserChartInfo($group,$match,$field,['user_id' =>1]);
        foreach ($data as &$v) {
            if($game_hall_id === '') {
                $v['game_hall_id'] = '';
                $v['game_hall_code'] = '';
            }
            if(!$game_id) {
                $v['game_name'] = '';
            }

            $v['valid_bet_score_total'] = number_format($v['valid_bet_score_total'], 2);
            $v['total_bet_score'] = number_format($v['total_bet_score'], 2);
            $v['total_win_score'] = number_format($v['total_win_score'], 2);
            if($is_export) {
                $v['game_hall_id'] = $v['game_hall_id'] ? trans('gamehall.'.$v['game_hall_id']) : trans('gamehall.all');
                $v['game_name'] = $v['game_name'] ? $v['game_name'] : trans('gamehall.all');
                unset($v['game_hall_code']);
            }
        }
        unset($v);
        //数据导出
        if($is_export) {
            $title = [
//                '玩家ID',
                trans('export.login_name'),
                trans('export.game_hall_title'),
                trans('export.game_title'),
                trans('export.cout_num'),
                trans('export.valid_bet_score'),
                trans('export.bet_score'),
                trans('export.win_score'),
            ];
            array_unshift($data, $title);
            $sub_title = trans('export.sub_player_title');
            $widths = [10,15,15,17,10,10,10];
            $header = trans('export.date_range').':'.$start_time.' - '.$end_time;
            $filename = $sub_title.'_'.date('Ymd',time()).time();

            $re = self::export($filename,$sub_title,$header,$data,$widths);

            return $this->response->array([
                'code' => 0,
                'text' => trans('agent.success'),
                'result' => [
                    'url' => 'http://'.$request->server("HTTP_HOST").'/'.$re['full']
                ],
            ]);

        } else {
            return $this->response->array([
                'code' => 0,
                'text' => trans('agent.success'),
                'result' => [
                    'data' => $data,
                ],
            ]);
        }

    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/totalBet/game",
     *   tags={"报表统计"},
     *   summary="查询游戏",
     *   description="
     *   成功返回字段说明
     *  {
        'code': 0,
        'text': '操作成功',
        'result': {
        'total': 5,
        'per_page': '1',
        'current_page': 1,
        'data': [
        {
        'game_id': 93,//游戏id
        'game_name': '龙虎 ',//游戏名称
        'game_round_num': 5,//局数
        'valid_bet_score_total': 2500,//有效投注额
        'total_bet_score': 2500,//投注额
        'operator_win_score': 100//商家盈利
        }
        ],
        'total_page_score': {//当前页的小计
        'game_round_num': 5,//总笔数
        'valid_bet_score_total': 2500,//总有效投注额
        'total_bet_score': 2500,//总投注额
        'operator_win_score': 100//商家盈利
        },
        'total_score': {//总计
        'game_round_num': 23,//总笔数
        'valid_bet_score_total': 6450,//总有效投注额
        'total_bet_score': 7500,//总投注额
        'operator_win_score': 350//商家盈利
        }
        }
        }
        导出返回格式：
        {
        'code': 0,
        'text': '操作成功',
        'result': {
        'url': 'http://agent.va/excel/查询指定游戏_20170413.xlsx'
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
     *     name="game_id",
     *     type="integer",
     *     description="游戏id",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="start_time",
     *     type="string",
     *     description="开始时间 2017-01-20 15:07:07",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="end_time",
     *     type="string",
     *     description="结束时间  2017-01-20 15:07:07",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="page",
     *     type="integer",
     *     description="当前页",
     *     required=false,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="page_num",
     *     type="integer",
     *     description="每页显示条数",
     *     required=false,
     *     default="10"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="is_export",
     *     type="integer",
     *     description="是否导出",
     *     required=false,
     *     default="0"
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function gameTotalBet(Request $request)
    {
        $game_id = $request->input('game_id');
        $start_time = $request->input('start_time');
        $end_time = $request->input('end_time');
        $page = $request->input('page',1);
        $page_num = $request->input('page_num',10);
        $is_export = $request->input('is_export',0);

        $skip = (int) ($page-1) * $page_num;
        $limit = (int) $page_num;
        //按游戏分组
        $group = [
            'game_id' => '$game_id',
        ];
        //显示的字段
        $field = [
//            'game_id' =>1,
            'game_name' =>1,
            'game_round_num' =>1,
            'valid_bet_score_total' =>1,
            'total_bet_score' =>1,
            'operator_win_score' =>1,
        ];
        //过滤
        $match = [
            'is_cancel'=>0,
        ];

        switch ($this->agentInfo['grade_id']) {
            //厅主
            case 1:
                $match['hall_id'] = $this->agentId;//过滤厅主
                break;
            //代理商
            case 2:
                $match['agent_id'] = $this->agentId;//过滤代理
                break;
        }

        if(isset($game_id) && !empty($game_id)) {
            $match['game_id'] = (int)$game_id;
        }

        if(isset($start_time) && !empty($start_time)) {
            $s_time = new \MongoDB\BSON\UTCDateTime(strtotime($start_time)* 1000);
            $match['start_time']['$gte'] = $s_time;
        }

        if(isset($end_time) && !empty($end_time)) {
            $e_time = new \MongoDB\BSON\UTCDateTime(strtotime($end_time)* 1000);
            $match['start_time']['$lt'] = $e_time;
        }
//        $data = self::getUserChartInfo($group,$match,$field,['game_id' =>1]);

        //数据导出
        if($is_export) {
            set_time_limit(0);
            ini_set('memory_limit','500M');
            $data = $count_data = self::getUserChartInfo($group, $match, $field,['game_id' =>1], 0, 1000);
            foreach ($data as &$v) {
                $v['valid_bet_score_total'] = number_format($v['valid_bet_score_total'],2);
                $v['total_bet_score'] = number_format($v['total_bet_score'],2);
                $v['operator_win_score'] = number_format($v['operator_win_score'],2);
            }
            unset($v);
            $total_data = self::getCountScore($count_data);
            $num = count($field)-count($total_data);
            $total_arr = array_merge(array_fill(0, $num, ""), $total_data);
            $total_arr[0] = 'Total';
            array_push($data, $total_arr);
            $title = [
//                '游戏ID',
                trans('export.game_title'),
                trans('export.cout_num'),
                trans('export.valid_bet_score'),
                trans('export.bet_score'),
                trans('export.win_score'),
            ];
            array_unshift($data, $title);
            $sub_title = trans('export.sub_game_title');
            $widths = [10,15,15,17,10];
            $header = trans('export.date_range').':'.$start_time.' - '.$end_time;
            $filename = $sub_title.'_'.date('Ymd',time()).time();

            $re = self::export($filename,$sub_title,$header,$data,$widths);

            return $this->response->array([
                'code' => 0,
                'text' => trans('agent.success'),
                'result' => [
                    'url' => 'http://'.$request->server("HTTP_HOST").'/'.$re['full']
                ],
            ]);

        } else {
            $total_data = self::getUserChartInfo($group, $match, $field,['game_id' =>1]);
            $data  = $count_data  = array_slice($total_data,$skip,$limit);
            foreach ($data as &$v) {
                $v['valid_bet_score_total'] = number_format($v['valid_bet_score_total'],2);
                $v['total_bet_score'] = number_format($v['total_bet_score'],2);
                $v['operator_win_score'] = number_format($v['operator_win_score'],2);
            }
            unset($v);
            return $this->response->array([
                'code' => 0,
                'text' => trans('agent.success'),
                'result' => [
                    'total' => count($total_data),
                    'per_page' => $page_num,
                    'current_page' => $page,
                    'data' => $data,
                    'total_page_score' => self::getCountScore($count_data),
                    'total_score' => self::getCountScore($total_data),
                ],
            ]);
        }

    }

    /**
     * 游戏数据报表分组查询
     * @param array $group mongo的分组 格式 ['game_hall_id' => '$game_hall_id']
     * @param array $match mongo的过滤 格式 ['is_cancel'=>0]
     * @param array $field 要显示的字段 ['hall_id' =>1]
     * @param array $sort 排序 ['hall_id' =>1] 正序：1，倒叙：-1
     * @param int $skip 从第几页开始
     * @param int $limit 取出条数

     * @return array
     */
    public static function getUserChartInfo(array $group, array $match, array $field, array $sort = [] ,  $skip = '', $limit = '') : array
    {
        $data = [];
        $aggregate = [];
        $match_ =  $match ? ['$match' => $match] : '';
        $skip_ = $skip ? ['$skip' => $skip] : '';
        $limit_ = $limit ? ['$limit' => $limit] : '';
        $sort_ = $sort ? ['$sort' => $sort] : '';
        $project_ = $field ? ['$project' => $field] : '';
        $group_ = '';
        if( $group ) {

            $groups = [
                '_id' => $group,
                'table_no' => ['$first' =>'$table_no'],
                'round_no' => ['$first' =>'$round_no'],
                'agent_id' => ['$first' =>'$agent_id'],
                'agent_name' => ['$first' =>'$agent_name'],
                'hall_id' => ['$first' =>'$hall_id'],
                'hall_name' => ['$first' =>'$hall_name'],
                'user_id' => ['$first' =>'$user_id'],
                'account' => ['$first' =>'$account'],
                'game_hall_id' => ['$first' =>'$game_hall_id'],
                'game_id' => ['$first' =>'$game_id'],
                'game_hall_code' => ['$first' =>'$game_hall_code'],
                'game_name' => ['$first' =>'$game_name'],
                'game_round_num' => ['$sum'  => 1],
                'valid_bet_score_total' => ['$sum'  => '$valid_bet_score_total'],
                'total_bet_score' => ['$sum'  => '$total_bet_score'],
                'total_win_score' => ['$sum'  => '$total_win_score'],
                'operator_win_score'=>['$sum'  => '$operator_win_score'],
            ];
            $group_ = ['$group' =>$groups];

        }

        $match_ && $aggregate[] = $match_;
        $group_ && $aggregate[] = $group_;
        $sort_ && $aggregate[] = $sort_;
        $limit_ && $aggregate[] = $limit_;
        $skip_ && $aggregate[] = $skip_;
        $project_ && $aggregate[] = $project_;
        $res = UserChartInfo::raw(function($collection) use($aggregate) {
            return $collection->aggregate($aggregate);
        });

        if($res) {
            $data = $res->toArray();

        }
        return $data;
    }


    /**
     * 导出excel
     * @param string $filename 保存的文件名
     * @param string $sub_title sheet标题
     * @param string $header 头部标题
     * @param array $data 数据
     * @param array $widhs 单元格宽度 [10,20]
     * @param string $FirstRowBackground 第一行背景颜色
     * @return mixed
     */
    private static function export(string $filename, string $sub_title, string $header, array $data, array $widhs=[],string $FirstRowBackground='#FFB6C1') : array
    {

        $re = Excel::create($filename, function($excel) use($data,$header,$sub_title,$widhs,$FirstRowBackground) {

            $excel->sheet($sub_title, function($sheet) use($data,$header,$sub_title,$widhs,$FirstRowBackground) {
                $column = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X'];
                //设置第一行头标题
                $sheet->row(1, array(
                    $header
                ));
                //设置第一行背景颜色
                /*$sheet->row(1, function($row) use($FirstRowBackground) {
                    $row->setBackground($FirstRowBackground);
                });*/

                //从第二行开始渲染数据
                $sheet->fromArray($data, null, 'A2', true, false);
                //第一行合并单元格
                $sheet->mergeCells('A1:'.$column[(Count($data[0])-1)].'1');
                //设置单元格宽度
                foreach ($widhs as $k => $v){
                    $sheet->setWidth($column[$k], $v);
                }
                //冻结第一行
                $sheet->freezeFirstRow();
            });

        })->store('xlsx', 'excel' , true);

        return $re;
    }

}