<?php
namespace App\Http\Controllers\V1;

use function foo\func;
use Illuminate\Http\Request;
use  App\Models\RedPacketsLog;
use  App\Models\RedPackets;

class RedPacketsController extends BaseController
{
    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/redPackets",
     *   tags={"查看厅主红包"},
     *   summary="查看单个厅主时间段红包详情",
     *   description="
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': {
    'total': 1,// 总页数
    'per_page': 10,// 每页数量
    'current_page': 1,// 当前页
    'data': [
    {
    'packets_amount': 21,//总金额
    'get_number': 4,// 获取个数
    'hall_name': 'hall_name',//厅主登录名
    'packets_id': 10,// 红包活动Id
    'user_number': 3,// 会员数量
    'packets_title': '我要发红包',//红包活动标题
    'start_date': '0000-00-00 00:00:00',//红包活动开始时间
    'end_date': '0000-00-00 00:00:00'// 红包结束时间
    }
    ],
    'total_page_score': {// 总计
    'get_amount_total': 21,//获取金额
    'get_number_total': 4,// 获取个数
    'get_user_total': 3//获取人数
    },
    'total_score': {// 小计
    'get_amount_total': 21,
    'get_number_total': 4,
    'get_user_total': 3
    }
    }
    }
    ",
     *   operationId="index",
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
     *     name="start_date",
     *     type="string",
     *     description="开始时间",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="end_date",
     *     type="string",
     *     description="结束时间",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="page",
     *     type="integer",
     *     description="当前页 默认1",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="page_num",
     *     type="integer",
     *     description="每页条数 默认10",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="is_page",
     *     type="integer",
     *     description="是否分页 1是 0否，默认为1",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function index(Request $request)
    {
        // 限制代理查看
        if($this->agentInfo['grade_id'] == 2) {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.grade_id_error'),
                'result' => '',
            ]);

        }

        // 获取请求参数
        $page = $request->input('page',1);
        $page_num = $request->input('page_num',10);
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $match['$match']['hall_id'] = $this->agentId;
        $skip = (int) ($page-1) * $page_num;
        $limit = (int) $page_num;

        //时间验证
        $db = RedPackets::select(['title', 'id','start_date','end_date']);
        if(!empty($end_date) && !empty($start_date) ){
            if(!$this->checkDate(['start_date'=>$request->input('start_date'),'end_date'=>$request->input('end_date')])) {
                return $this->response()->array([
                    'code' => 400,
                    'text' => trans('maintain.end_date.end_lt'),
                    'result' => ''
                ]);
            }
            $db->where('start_date','>=',$start_date)->where('end_date','<=',$end_date);
        }

        // 获取该时间段内的所有红包活动id
        $red_pkg_info = $db->get();
        $id_arr = array_column( $red_pkg_info->toArray(),'id');
        $match['$match']['packets_id'] = ['$in'=>$id_arr];

        // 获取当前厅主红包总数量和总金额
        $total_group = ['$group'=> [
            '_id' => null ,
            'packets_amount' => ['$sum'=>'$packets_amount'],
            'get_number'=> ['$sum' => '$get_number'],
            'get_user_total'=> ['$addToSet' => '$user_id'],
        ] ];
        $total_aggregate =[$match,$total_group,['$project' => ['_id'=>0] ]];
        $count_user_data = RedPacketsLog::raw()->aggregate($total_aggregate)->toArray();

        // 无领取人数
        if (!isset($count_user_data[0]->get_user_total)){
            return  $this->response()->array([
                'code'          => 400,
                'text'          => trans('delivery.empty_list'),
                'result'        => ''
            ]);
        }

        // 统计领取总会员数量
        $count_user_data =$count_user_data[0];
        $count_user_data->get_user_total = count($count_user_data->get_user_total);

        // 查询统计厅主红包信息
        $skip = ['$skip'=> $skip ];
        $limit = ['$limit'=> $limit];
        $sort = ['$sort'=> ['create_date'=> -1 ] ];
        $group = ['$group'=> [
            '_id' =>  '$packets_id',
            'packets_amount' => ['$sum'=>'$packets_amount'],
            'get_number'=> ['$sum' => '$get_number'],
            'hall_name'=> ['$first' => '$hall_name'],
            'packets_id'=> ['$first' => '$packets_id'],
            'user_number'=> ['$addToSet' => '$user_id'],
        ] ];
        $aggregate = [$match,$group, $sort, $skip,$limit];
        $total_data = RedPacketsLog::raw()->aggregate($aggregate)->toArray();
        if(empty($total_data)) {
            return  $this->response()->array([
                'code'          => 400,
                'text'          => trans('delivery.empty_list'),
                'result'        => ''
            ]);
        }

        //将数据红包信息合并
        $pkg_data = [];
        $pkg_user =[];
        foreach ($total_data  as $i => $val){
            foreach ($red_pkg_info  as $j => $item){
                if($val->packets_id == $item->id){
                    $pkg_data[] = [
                        'packets_title'=> $item->title,
                        'start_date'=> $item->start_date,
                        'end_date'=> $item->end_date,
                        'packets_amount'=> $val->packets_amount,
                        'get_number'=> $val->get_number,
                        'hall_name'=> $val->hall_name,
                        'packets_id'=> $val->packets_id,
                        'user_number'=> count($val->user_number) ,
                    ];
                    $pkg_user= array_merge($pkg_user, (array)$val->user_number);
                }
            }
        }

        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => [
                'total' => count($total_data),
                'per_page' => $page_num,
                'current_page' => $page,
                'data' => $pkg_data,
                'total_page_score' => self::getCountScore($pkg_data,$pkg_user),
                'total_score' => $count_user_data,
            ],
        ]);

    }


    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="redPackets/showDetail/{packet_id}",
     *   tags={"查看厅主红包"},
     *   summary="查看单个厅主时间段会员红包详情",
     *   description="
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': {
    'total': 1,// 总数
    'per_page': 10,// 每页数量
    'current_page': 1,//当前页
    'data': [
    {
    'packets_amount': 11,//总金额
    'get_number': 3, //总数量
    'agent_name': 'agent_name_1',//代理登录名
    'user_name': 'test1'//玩家登录名
    }
    ],
    'total_page_score': {// 总计
    'get_amount_total': 11,// 总计金额
    'get_number_total': 3,// 总计数量
    'get_user_total': 1// 总计人数
    },
    'total_score': {// 小计
    'get_amount_total': 11,// 小计总金额
    'get_number_total': 3,//小计总数量
    'get_user_total': 1 //小计玩家数量
    }
    }
    }
    ",
     *   operationId="index",
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
     *     name="start_date",
     *     type="string",
     *     description="开始时间 (在厅主列表有时间参数则详情也必须有时间参数)",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="end_date",
     *     type="string",
     *     description="结束时间(同上)",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="page",
     *     type="integer",
     *     description="当前页 默认1",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="page_num",
     *     type="integer",
     *     description="每页条数 默认10",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="is_page",
     *     type="integer",
     *     description="是否分页 1是 0否，默认为1",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function showDetail(Request $request, int $packet_id)
    {
        $data = RedPackets::find($packet_id);
        if( ! $data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.data_not_exist'),
                'result' => '',
            ]);
        }

        $page = $request->input('page',1);
        $page_num = $request->input('page_num',10);
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $startDate_utc = strtotime($start_date) * 1000;
        $endDate_utc = strtotime($end_date) * 1000;
        $match['$match']['hall_id'] = $this->agentId;
        $match['$match']['packets_id'] =  $packet_id;
        $skip = (int) ($page-1) * $page_num;
        $limit = (int) $page_num;

        if(!empty($end_date) && !empty($start_date) ){
            if(!$this->checkDate(['start_date'=>$request->input('start_date'),'end_date'=>$request->input('end_date')])) {
                return $this->response()->array([
                    'code' => 400,
                    'text' => trans('maintain.end_date.end_lt'),
                    'result' => ''
                ]);
            }

            $match['$match'] ['create_date'] = ['$gte'=> new \MongoDB\BSON\UTCDateTime($startDate_utc), '$lte'=>new \MongoDB\BSON\UTCDateTime($endDate_utc)];
        }

        // 查询单个红包单个用户的领取详情
        $project = ['$project'=> [ 'agent_name'=> 1,  'packets_amount'=>1, 'get_number'=>1, 'user_name'=>1]];
        $sort = ['$sort'=> ['packets_amount'=>1] ];
        $group = ['$group'=> [
            '_id' => ['user_id'=>'$user_id'],// 以用户为单位对单个红包活动进行分组
            'packets_amount' => ['$sum'=>'$packets_amount'],
            'get_number'=> ['$sum' => '$get_number'],
            'agent_name'=> ['$first' => '$agent_name'],
            'user_name'=> ['$first' => '$user_name'],
        ] ];

        $aggregate = [$match,$group, $sort, $project];
        $total_data = RedPacketsLog::raw(function($collection) use($aggregate) {
            return $collection->aggregate($aggregate);
        })->toArray();

        $data = $count_data = array_slice($total_data,$skip,$limit);

        if(empty($total_data) || empty($data)) {
            return  $this->response()->array([
                'code'          => 400,
                'text'          => trans('delivery.empty_list'),
                'result'        => ''
            ]);
        }

        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => [
                'total' => count($total_data),
                'per_page' => $page_num,
                'current_page' => $page,
                'data' => array_values($data),
                'total_page_score' => self::getPerCountScore($data),
                'total_score' => self::getPerCountScore($total_data),
            ],
        ]);
    }


    //验证查询时间
    private function checkDate($data)
    {
        if(empty($data))
            return false;

        $start_date = $data['start_date'];
        $end_date = $data['end_date'];

        //开始时间不能大于结束时间
        if(strtotime($end_date) <= strtotime($start_date))
            return false;
        return true;
    }

    // 时段内所有厅主小计 总计 计算
    private static function getCountScore($data,$pkg_user)
    {
        $total = [
            'get_amount_total' => 0,
            'get_number_total' => 0,
            'get_user_total' => count(array_unique($pkg_user)) ,
        ];
        if( $data ) {

            foreach ($data as $k => $v) {
                $total['get_amount_total'] += $v['packets_amount'];
                $total['get_number_total'] += $v['get_number'];
            }
        }

        return $total;
    }

    // 时段内单个厅主小计 总计 计算
    private static function getPerCountScore($data)
    {
        $total = [
            'get_amount_total' => 0,
            'get_number_total' => 0,
            'get_user_total' => count($data),
        ];
        if( $data ) {

            foreach ($data as $k => $v) {
                $total['get_amount_total'] += $v['packets_amount'];
                $total['get_number_total'] += $v['get_number'];
            }
        }

        return $total;
    }

}