<?php
namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Models\CashRecord;
use App\Models\Player;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;
/**
 * Class CashRecordController
 * @package App\Http\Controllers\V1
 * @desc 现金流
 */
class CashRecordController extends BaseController
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
     *   path="/cashRecord",
     *   tags={"报表统计"},
     *   summary="查询现金流",
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
            '_id': '597ad7c8e1382314682fd841',//单号
            'cash_no': '923d3f9f07325ce4'//局ID（流水号）
            'user_name': 'csj_play111',//玩家名称
            'type': 操作类型,1转帐,2打赏,3优惠退水,4线上变更,5公司入款,6优惠冲销,7视讯派彩,8系统取消出款,9系统拒绝出款,10取消派彩变更,21旗舰厅下注，22为至尊厅下注，23为金臂厅下注，24为贵宾厅下注,31视讯取消退回,32旗舰厅取消退回,33金臂厅取消退回,34至尊厅取消退回,35贵宾厅取消退回
            'amount': '-10',//加减的金额
            'user_money': 1980,//用户余额
            'add_time': '2017-03-28 08:00:00'//添加时间
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
     *     name="uid",
     *     type="integer",
     *     description="玩家id",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="_id",
     *     type="string",
     *     description="单号",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="user_name",
     *     type="string",
     *     description="玩家登录名",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="type",
     *     type="string",
     *     description="操作类型, 1：api转入，2：api转出 ，3：人工操作（包括扣钱和加钱），4：下注(这个包括下注和派彩的记录）",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="time_type",
     *     type="integer",
     *     description="时间类型 1：传时间段，2：具体时间段",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="time_area",
     *     type="integer",
     *     description="当time_type=1时有效, 时间区 1：三天内，2：一周内，3：一个月内",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="start_time",
     *     type="string",
     *     description="当time_type=2时有效, 开始时间 格式：2017-03-21 10:41:12",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="end_time",
     *     type="string",
     *     description="当time_type=2时有效, 结束时间 格式：2017-03-21 10:41:12",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="page",
     *     type="integer",
     *     description="当前页 默认为1",
     *     required=false,
     *     default="1"
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
     *     name="is_export",
     *     type="integer",
     *     description="是否导出 0不导出，1导出，默认为0不导出",
     *     required=false,
     *     default="0"
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function index(Request $request)
    {
        $_id = $request->input('_id');
        $uid = $request->input('uid');
        $type = $request->input('type');
        $user_name = $request->input('user_name');
        $cash_no = $request->input('cash_no');
        $time_type = $request->input('time_type');
        $time_area = $request->input('time_area');
        $start_time = $request->input('start_time');
        $end_time = $request->input('end_time');
        $page_num = $request->input('page_num', env('PAGE_NUM'));
        $is_export = $request->input('is_export');

        $obj = CashRecord::select('uid','user_name','type','amount','status','user_money','add_time','cash_no','order_sn','connect_mode');
        $obj1 = CashRecord::select('uid','user_name','type','amount','status','user_money','add_time','cash_no','order_sn','connect_mode');

        switch ($this->agentInfo['grade_id']) {
            case 1:
                $obj->where('hall_id',  (int)$this->agentId);
                $obj1->where('hall_id',  (int)$this->agentId);
                break;
            case 2:
                $obj->where('agent_id',  (int)$this->agentId);
                $obj1->where('agent_id',  (int)$this->agentId);
                break;
        }

        if(isset($_id) && !empty($_id)) {
            $obj->where('_id',  $_id);
            $obj1->where('_id',  $_id);
        }

        if(isset($uid) && !empty($uid)) {
            $obj->where('uid',  (int)$uid);
            $obj1->where('uid',  (int)$uid);
        }

        if(isset($user_name) && !empty($user_name)) {
            $obj->where('user_name',  $user_name);
            $obj1->where('user_name',  $user_name);
        }

        if(isset($cash_no) && !empty($cash_no)) {

            if(strstr($cash_no, 'LA')) {

                $obj->where('order_sn',$cash_no);
                $obj1->where('order_sn',$cash_no);

            } else {

                $obj->where('cash_no',  $cash_no);
                $obj1->where('cash_no',  $cash_no);
            }

        }

        if(isset($type) && !empty($type)) {
            switch ($type) {
                //api转入
                case 1:
                    $obj->where('type', 1);
                    $obj1->where('type', 1);
                    $obj->where('status', 3);
                    $obj1->where('status', 3);
                    break;
                //api转出
                case 2:
                    $obj->where('type', 1);
                    $obj1->where('type', 1);
                    $obj->where('status', 4);
                    $obj1->where('status', 4);
                    break;
                //人工操作（包括扣钱和加钱）
                case 3:
                    $obj->where('type',  4);
                    $obj1->where('type',  4);
                    break;
                //下注(这个包括下注和派彩的记录）
                case 4:
                    $obj->whereIn('type',  [7,10,21,22,23,24,31,32,33,34,35]);
                    $obj1->whereIn('type',  [7,10,21,22,23,24,31,32,33,34,35]);
                    break;
                //红包
                case 5:
                    $obj->where('type',  36);
                    $obj1->where('type',  36);
                    break;
            }

        }

        if($time_type == 1) {

            switch ($time_area) {
                case 1:
                    //三天内
                    $time = self::getUTCDateTime(3);

                    break;
                case 2:
                    //一周内
                    $time = self::getUTCDateTime(7);
                    break;
                case 3:
                    //一月内
                    $time = self::getUTCDateTime(30);
                    break;
                default :
                    return $this->response->array([
                        'code'=>0,
                        'text'=> trans('agent.param_error'),
                        'result'=>'',
                    ]);
                    break;
            }

            if($time != '') {

                $obj->where('add_time',  '>=', $time[0]);
                $obj1->where('add_time',  '>=', $time[0]);
                $obj->where('add_time',  '<', $time[1]);
                $obj1->where('add_time',  '<', $time[1]);

            }

        }

        if($time_type == 2) {

            if(isset($start_time) && !empty($start_time)) {
                $s_time = Carbon::parse($start_time)->timestamp;
//                $s_time = strtotime($start_time);
                $obj->where('add_time', '>=', new \MongoDB\BSON\UTCDateTime($s_time * 1000));
                $obj1->where('add_time', '>=', new \MongoDB\BSON\UTCDateTime($s_time * 1000));
            }

            if(isset($end_time) && !empty($end_time)) {
                $e_time = Carbon::parse($end_time)->timestamp + 1;//应对经过换算后精度损耗的问题，加多1S
                $obj->where('add_time', '<',new \MongoDB\BSON\UTCDateTime($e_time * 1000));
                $obj1->where('add_time', '<',new \MongoDB\BSON\UTCDateTime($e_time * 1000));

            }
        }
        $obj->orderby('add_time','desc');
        $obj1->orderby('add_time','desc');


        if($is_export) {

            $export_count = $obj->count();

            if($export_count) {
                $filename = './excel/CashRecord_'.date('Ymd',time()).time().'.csv';
                $title = [
                    '登录名',
                    '单号',
                    '局ID(流水号)',
                    '加减金额',
                    '剩余额度',
                    '操作类型',
                    '操作时间(美东时间)',
                ];

                $pre_count = 30000;
                $export_count = $pre_count*10;
                set_time_limit(0);
                ini_set('memory_limit','500M');
                if ( File::exists( $filename) ){
                    File::delete($filename);
                }
                // 打开PHP文件句柄
                $fp = fopen($filename, 'a');
                // 将中文标题转换编码，否则乱码
                foreach ($title as $i => $v) {
                    $title[$i] = iconv('utf-8', 'GB18030', $v);
                }
                // 将标题名称通过fputcsv写到文件句柄
                fputcsv($fp, $title);


                //intval($export_count / $pre_count) +
                for ( $i = 0; $i < intval($export_count / $pre_count); $i++ ) {
                    $data = $obj1->offset($i*$pre_count)->limit($pre_count)->get()->toArray();
                    if($data){
                        $data = self::dataHandle($data, 1);
                        foreach ( $data as $item ) {
                            $rows = array();
                            foreach ( $item as $export_obj){
                                $rows[] = iconv('utf-8', 'GB18030', ' '.$export_obj.' ');
                            }
                            fputcsv($fp, $rows);
                        }
                        unset($data);
                        ob_flush();
                        flush();
                    } else {
                        break;
                    }

                }

                return $this->response->array([
                    'code' => 0,
                    'text' => trans('agent.success'),
                    'result' => [
                        'url' => 'http://'.$request->server("HTTP_HOST").'/'.$filename
                    ],
                ]);
                /*
                $data = $data['data'];
                $filename = 'agent_cash_record_'.date('Ymd',time());
                $title = [
                    '玩家ID',
                    '登录名',
                    '操作类型',
                    '加减金额',
                    '剩余金额',
                    '操作时间',
                ];
                array_unshift($data, $title);
                $re = Excel::create($filename, function($excel) use($data) {

                    $excel->sheet('现金流', function($sheet) use($data) {
                        $sheet->fromArray($data, null, 'A1', true, false);
                        $sheet->freezeFirstRow();
                        $sheet->setWidth('A', 10);
                        $sheet->setWidth('B', 15);
                        $sheet->setWidth('C', 20);
                        $sheet->setWidth('F', 20);
                    });

                })->store('xlsx', 'excel' , true);

                return $this->response->array([
                    'code' => 0,
                    'text' => trans('agent.success'),
                    'result' => [
                        'url' => 'http://'.$request->server("HTTP_HOST").'/'.$re['full']
                    ],
                ]);*/

            } else {

                return $this->response->array([
                    'code'=>400,
                    'text'=> trans('agent.no_data_export'),
                    'result'=>'',
                ]);

            }

        }
        $data = $obj->paginate((int)$page_num)->toArray();
        $data['data'] = self::dataHandle($data['data']);
        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>$data,
        ]);
    }

    /**现金流数据转换
     * @param array $data 数据
     * @param int $is_export 是否导出数据 1是，0否
     * @return array
     */
    private function dataHandle( array $data, int $is_export=0) : array
    {
        if($data) {

            foreach ($data as &$v){
                $v['add_time'] = $v['add_time']->__tostring();
                $v['add_time'] = date('Y-m-d H:i:s',$v['add_time']/1000);
                if($v['status'] == 3) {
                    $v['amount'] = '+'.number_format($v['amount'],2);
                }
                if($v['status'] == 4) {
                    $v['amount'] = '-'.number_format($v['amount'],2);
                }
                $v['user_money'] = number_format($v['user_money'],2);

                if(in_array($v['type'],[1,4,5]))
                {
                    $v['cash_no'] = $v['order_sn'];
                }

                !isset($v['cash_no']) && $v['cash_no'] = "";

                if($is_export) {
                    switch ($v['type']) {
                        case 1:
                            $v['type'] = '转账';
                            break;
                        case 2:
                            $v['type'] = '打赏';
                            break;
                        case 3:
                            $v['type'] = '优惠退水';
                            break;
                        case 4:
                            $v['type'] = '线上变更';
                            break;
                        case 5:
                            $v['type'] = '公司入款';
                            break;
                        case 6:
                            $v['type'] = '优惠冲销';
                            break;
                        case 7:
                            $v['type'] = '视讯派彩';
                            break;
                        case 8:
                            $v['type'] = '系统取消出款';
                            break;
                        case 9:
                            $v['type'] = '系统拒绝出款';
                            break;
                        case 21:
                            $v['type'] = '旗舰厅下注';
                            break;
                        case 22:
                            $v['type'] = '至尊厅下注';
                            break;
                        case 23:
                            $v['type'] = '金臂厅下注';
                            break;
                        case 24:
                            $v['type'] = '贵宾厅下注';
                            break;
                        case 31:
                            $v['type'] = '视讯取消退回';
                            break;
                        case 32:
                            $v['type'] = '旗舰厅取消退回';
                            break;
                        case 33:
                            $v['type'] = '金臂厅取消退回';
                            break;
                        case 34:
                            $v['type'] = '至尊厅取消退回';
                            break;
                        case 35:
                            $v['type'] = '贵宾厅取消退回';
                        case 36:
                            $v['type'] = '红包';
                    }
                }
//                unset($v['order_sn']);
                unset($v['status']);

                $v = [
//                    'uid' => $v['uid'],
                    'user_name' => $v['user_name'],
                    '_id' => $v['_id'],
                    'cash_no' => $v['cash_no'],
                    'amount' => $v['amount'],
                    'user_money' => $v['user_money'],
                    'type' => $v['type'],
                    'add_time' => $v['add_time'],
                    'connect_mode' => isset($v['connect_mode']) ? $v['connect_mode'] : 0,//添加扣费模式字段

                ];
            }

            unset($v);
            return $data;
        }
        return [];
    }

    /**
     * 返回mongo的UTC时间
     * @param int $day 几天内
     */
    private function getUTCDateTime(int $day)
    {
        if($day) {
            $start_d = (new Carbon('-'.$day.' day'))->startOfDay()->timestamp;
            $end_d = (new Carbon())->timestamp;
            $s_time = new \MongoDB\BSON\UTCDateTime($start_d * 1000);
            $e_time = new \MongoDB\BSON\UTCDateTime($end_d * 1000);
            return [
                $s_time,
                $e_time
            ];
        } else {
            return '';
        }


    }
}