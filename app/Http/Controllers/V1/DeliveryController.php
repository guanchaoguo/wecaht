<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkj.com
 * Date: 2017/4/5
 * Time: 10:57
 * 厅主交收控制器
 */
namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends BaseController
{
    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/issue",
     *   tags={"交收统计"},
     *   summary="期数列表",
     *   description="
     *   期数列表
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
    'id': 3,            //ID
    'issue': '201703',  //期数名称
    'start_date': '2017-04-03 00:00:00',    //开始时间
    'end_date': '2017-04-04 00:00:00',      //结束时间
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
     *     name="issue",
     *     type="string",
     *     description="期数",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="start_date",
     *     type="string",
     *     description="开始时间",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="end_date",
     *     type="string",
     *     description="结束时间",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="page",
     *     type="string",
     *     description="当前页",
     *     required=false,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="page_num",
     *     type="string",
     *     description="每页条数",
     *     required=false,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="is_page",
     *     type="number",
     *     description="是否分页,是否分页 1：是，0：否 ，默认1",
     *     required=false,
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
    public function index(Request $request)
    {
        if($this->agentInfo['grade_id'] == 2) {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.grade_id_error'),
                'result' => '',
            ]);
        }

        $issue = $request->input('issue');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $page_num = $request->input('page_num',10);
        $is_page = $request->input('is_page', 1);

        //结束时间不能小于等于开始时间
        if(!empty($start_date) && !empty($end_date) && strtotime($start_date) >= strtotime($end_date))
        {
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('delivery.end_date.le_start'),
                'result'    => ''
            ]);
        }
        $db = DB::table('game_platform_delivery');
        if(!empty($issue))
        {
            $db->where('issue','like','%'.$issue.'%');
        }

        if(!empty($start_date))
        {
            $db->where('start_date','>=',$start_date);
        }
        if(!empty($end_date))
        {
            $db->where('end_date','<=',$end_date);
        }

        $field = ['id','issue','start_date','end_date'];
        if(!$is_page) {
            $list = $db->select($field)->get()->toArray();
            $res['data'] = $list;
        } else {
            $res = $db->select($field)->paginate($page_num)->toArray();
        }
        if(!$res['data'])
        {
            return  $this->response()->array([
                'code'          => 0,
                'text'          => trans('delivery.empty_list'),
                'result'        => $res
            ]);
        }

        /*foreach ($res['data'] as $k=>&$v)
        {
            $v->start_date = $v->local_start_date;
            $v->end_date = $v->local_end_date;
        }*/

        return  $this->response()->array([
            'code'          => 0,
            'text'          => trans('delivery.success'),
            'result'        => $res
        ]);
    }


    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/delivery",
     *   tags={"交收统计"},
     *   summary="交收列表",
     *   description="
     *   交收列表
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
    'id': 1, //id
    'issue': '201701', //期数
    'p_name': 'agent_test', //厅主名称
    'p_id': 2, //厅主ID
    'real_income': '240000.00'  //应交收金额
    'platform_profit': '100000.00', //期数对应厅主毛利润
    'scale': '10.00%', //平台占成比例
    'receipt': '10000.00', //游戏平台应收费用
    'roundot': '1000.00', //包网费用
    'line_map': '2000.00', //线路图
    'upkeep': '2000.00', //维护费
    'ladle_bottom': '3000.00', //包底费用
    'is_over': 1 //是否已经交收标记，0为否，1为真
    }
    ],
    'total_receipt': 18000 //应收总额
    'total_real': 18000 //实收总额
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
     *     name="issue",
     *     type="string",
     *     description="期数",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="page",
     *     type="string",
     *     description="当前页",
     *     required=false,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="page_num",
     *     type="string",
     *     description="每页条数",
     *     required=false,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="is_page",
     *     type="number",
     *     description="是否分页,是否分页 1：是，0：否 ，默认1",
     *     required=false,
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
    public function issueList(Request $request)
    {
        if($this->agentInfo['grade_id'] == 2) {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.grade_id_error'),
                'result' => '',
            ]);
        }

        $issue = $request->input('issue');
        $page_num = $request->input('page_num',10);
        $is_page = $request->input('is_page', 1);
        $db = DB::table('game_platform_delivery_info');

        $db->where('p_id','=',$this->agentId);

        if(!empty($issue))
        {
            $db->where('issue','=',$issue);
        }

        $db->orderby('id', 'desc');

        if(!$is_page)
        {
            $list = $db->get()->toArray();
            $res['data'] = $list;
        }
        else
        {
            $res = $db->paginate($page_num)->toArray();
        }

        if(!$res['data'])
        {
            return  $this->response()->array([
                'code'          => 0,
                'text'          => trans('delivery.empty_list'),
                'result'        => $res
            ]);
        }


        //统计当前条件总的交收数据
        $res['total_receipt'] = 0.00;
        $res['total_real'] = 0.00;
        foreach ($res['data'] as $key=>$val)
        {
            //计算每个厅主每一期的应收款项
            $res['data'][$key]->real_income = number_format(($val->receipt > $val->ladle_bottom ? $val->receipt : $val->ladle_bottom) + $val->roundot + $val->line_map + $val->upkeep,2);
            //统计总的应收款
            $res['total_receipt'] += ($val->receipt > $val->ladle_bottom ? $val->receipt : $val->ladle_bottom) + $val->roundot + $val->line_map + $val->upkeep;
            $res['data'][$key]->scale = $val->scale;
            //统计实收金额
            if($val->is_over == 1)
            {
                $res['total_real'] += ($val->receipt > $val->ladle_bottom ? $val->receipt : $val->ladle_bottom) + $val->roundot + $val->line_map + $val->upkeep;
            }
            $val->is_over = (bool)$val->is_over;
        }
        $res['total_real'] = number_format($res['total_real'],2);
        $res['total_receipt'] = number_format($res['total_receipt'],2);
        return  $this->response()->array([
            'code'          => 0,
            'text'          => trans('delivery.success'),
            'result'        => $res
        ]);
    }
}
