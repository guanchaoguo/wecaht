<?php
/**
 * Created by PhpStorm.
 * User: Sanji
 * Date: 2017/10/16
 * Time: 9:56
 */
namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use MongoDB\BSON\UTCDateTime;
use SwaggerTests\ValidateRelationsTest;

class MonitorController  extends BaseController
{

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/monitor",
     *   tags={"监控管理"},
     *   summary="监控管理列表",
     *   description="
     *   监控管理列表
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': {
    'data': [
    {
    'id': 67,
    'hall_id': 1,
    'name': '刷水',
    'tag': 'M001',
    'status': 0,
    'rule': []
    },
    {
    'id': 68,
    'hall_id': 1,
    'name': '大额投注',
    'tag': 'M002',
    'status': 0,
    'rule': {
    'bet': 2000,
    'gap': 999
    }
    },
    {
    'id': 69,
    'hall_id': 1,
    'name': '高盈利',
    'tag': 'M003',
    'status': 0,
    'rule': {
    'profit': 1005,
    'gap': 50
    }
    },
    {
    'id': 70,
    'hall_id': 1,
    'name': '连胜次数',
    'tag': 'M004',
    'status': 0,
    'rule': {
    'win_streak': 1,
    'gap': 5
    }
    },
    {
    'id': 71,
    'hall_id': 1,
    'name': '胜率',
    'tag': 'M005',
    'status': 0,
    'rule': {
    'victory_ratio': 200,
    'gap': 5
    }
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
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="locale",
     *     type="string",
     *     description="语言标识",
     *     required=true,
     *     default="Accept:application/vnd.agent.v1+json"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="page_num",
     *     type="string",
     *     description="分页条数",
     *     required=true,
     *     default="10"
     *   ),
     *  @SWG\Parameter(
     *     in="formData",
     *     name="token",
     *     type="string",
     *     description="token",
     *     required=true,
     *     default="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vYWdlbnQuZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNTA4NDY1NTUzLCJleHAiOjE1MDg2ODE1NTMsIm5iZiI6MTUwODQ2NTU1MywianRpIjoiaUxUcXd1VVhYZUcybzFuOSIsInN1YiI6NDY1fQ.ojgS4y1org7pjR3_h1_Jd2BWONBo2ruNrrjPP9bw4fY"
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function list(Request $request)
    {
        $res = DB::table("sys_monitor")->where(['hall_id'=>$this->agentId])->get()->toArray();
        //var_export($res);die;
        if($res)
        {
            foreach ($res as $k=>&$v)
            {
                //获取对应的规则
               $rule =  DB::table("sys_monitor_rule")->where(["tag"=>$v->tag,'hall_id'=>$this->agentId])->get()->toArray();
                if(!$rule) {
                    $v->rule = [];
                    continue;
                }
                foreach ($rule as $k1=>$v1)
                {
                    $v->rule[$v1->keycode] = $v1->value;
                }
            }
        }
        return $this->response->array([
            'code' => 0,
            'text' =>trans('monitor23.success'),
            'result' => [
                'data' => $res,
            ],
        ]);
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Put(
     *   path="/monitor",
     *   tags={"监控管理"},
     *   summary="设置单个监控项参数",
     *   description="
     *   设置单个监控项参数
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': ''
    }
    ",
     *   operationId="monitor",
     *   @SWG\Parameter(
     *     in="header",
     *     name="Accept",
     *     type="string",
     *     description="http头信息",
     *     required=true,
     *     default="Accept:application/vnd.agent.v1+json"
     *   ),
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="locale",
     *     type="string",
     *     description="语言标识",
     *     required=true,
     *     default="Accept:application/vnd.agent.v1+json"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="tag",
     *     type="string",
     *     description="监控项标识符",
     *     required=true,
     *     default="M002"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="rule",
     *     type="string",
     *     description="具体监控项规则，数组格式例如",
     *     required=true,
     *     default="100"
     *   ),
     *  @SWG\Parameter(
     *     in="formData",
     *     name="token",
     *     type="string",
     *     description="token",
     *     required=true,
     *     default="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vYWdlbnQuZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNTA4NDY1NTUzLCJleHAiOjE1MDg2ODE1NTMsIm5iZiI6MTUwODQ2NTU1MywianRpIjoiaUxUcXd1VVhYZUcybzFuOSIsInN1YiI6NDY1fQ.ojgS4y1org7pjR3_h1_Jd2BWONBo2ruNrrjPP9bw4fY"
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function setMonitor(Request $request)
    {
        $tag = $request->input('tag');
        $rule = $request->input('rule');
        if(!$tag || !is_array($rule))
        {
            return $this->response->array([
                'code' => 0,
                'text' =>trans('monitor23.invalid_error'),
                'result' => ''
            ]);
        }
        //校验具体规则值是否有填写
        $is_true = true;
        foreach ($rule as $k=>$v)
        {
            if (empty($v))
            {
                $is_true = false;
                break;
            }
        }
        if(!$is_true)
        {
            return $this->response->array([
                'code' => 0,
                'text' =>trans('monitor23.not_null'),
                'result' => ''
            ]);
        }

        //查看修改的规则是否存在
        $list = DB::table("sys_monitor_rule")->where(["tag"=>$tag,'hall_id'=>$this->agentId])->get()->toArray();
        if(!$list)
        {
            return $this->response->array([
                'code' => 0,
                'text' =>trans('monitor23.not_exists'),
                'result' => ''
            ]);
        }

        //进行记录的修改（循环修改，不进行删除后添加，防止监控服务器出错）,事物处理
        DB::beginTransaction();
        foreach ($rule as $k=>$v)
        {
            $update = DB::table("sys_monitor_rule")->where(["tag"=>$tag,"keycode"=>$k,'hall_id'=>$this->agentId])->update(["value"=>$v,'last_date'=>date("Y-m-d H:i:s",time())]);
            if(!$update)
            {
                DB::rollBack();
            }
        }

        //更新redis数据
        $redisStatus = $this->setRedis($tag);
        if(!$redisStatus)
        {
            DB::rollBack();

            //提示操作失败
            return $this->response->array([
                'code' => 400,
                'text' =>trans('monitor23.fails'),
                'result' => ''
            ]);
        }
        DB::commit();



        return $this->response->array([
            'code' => 0,
            'text' =>trans('monitor23.success'),
            'result' =>""
        ]);

    }

    //监控规则或者监控状态修改时同步信息到redis
    private function setRedis($tag)
    {
        //更新redis数据
        $find = DB::table("sys_monitor")->where(["tag"=>$tag,'hall_id'=>$this->agentId])->select('*')->first();
        $ruleList = DB::table("sys_monitor_rule")->where(["tag"=>$tag,'hall_id'=>$this->agentId])->select('tag','keycode','value')->get()->toArray();
        foreach ($ruleList as $k=>$v)
        {
            $hashData[$v->keycode] = $v->value;
            $hashData['tag'] = $v->tag;
        }
        $hashData['status'] = $find->status;
        $redis = Redis::connection("monitor");
        $res = $redis->hMset(env('MONITOR_RULE').":".$tag.":$this->agentId",$hashData);
        if(!$res)
        {
            return false;
        }
        return true;
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Put(
     *   path="/monitor/status",
     *   tags={"监控管理"},
     *   summary="设置单个监控项的状态",
     *   description="
     *   设置单个监控项的状态
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': ''
    }
    ",
     *   operationId="monitor",
     *   @SWG\Parameter(
     *     in="header",
     *     name="Accept",
     *     type="string",
     *     description="http头信息",
     *     required=true,
     *     default="Accept:application/vnd.agent.v1+json"
     *   ),
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="locale",
     *     type="string",
     *     description="语言标识",
     *     required=true,
     *     default="Accept:application/vnd.agent.v1+json"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="tag",
     *     type="string",
     *     description="监控项标识符",
     *     required=true,
     *     default="M002"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="status",
     *     type="number",
     *     description="应用状态，0为关闭，1为开启，默认为0",
     *     required=true,
     *     default="0"
     *   ),
     *  @SWG\Parameter(
     *     in="formData",
     *     name="token",
     *     type="string",
     *     description="token",
     *     required=true,
     *     default="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vYWdlbnQuZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNTA4NDY1NTUzLCJleHAiOjE1MDg2ODE1NTMsIm5iZiI6MTUwODQ2NTU1MywianRpIjoiaUxUcXd1VVhYZUcybzFuOSIsInN1YiI6NDY1fQ.ojgS4y1org7pjR3_h1_Jd2BWONBo2ruNrrjPP9bw4fY"
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function setStatus(Request $request)
    {
        $tag = $request->input('tag');
        $status = $request->input('status',0);

        //判断记录是否存在
        $find = DB::table("sys_monitor")->where(['tag'=>$tag,'hall_id'=>$this->agentId])->first();
        if(!$find)
        {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('monitor23.not_exists'),
                'result' =>""
            ]);
        }
        //判断状态字段是否合法
        if(!in_array($status,[0,1]))
        {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('monitor23.invalid_error'),
                'result' => ''
            ]);
        }

        //进行正常修改操作
        DB::beginTransaction();
        $res = DB::table("sys_monitor")->where(['tag'=>$tag,'hall_id'=>$this->agentId])->update(['status'=>$status]);

        //更新redis数据
        $redisStatus = $this->setRedis($tag);
        if(!$redisStatus || !$res)
        {
            DB::rollBack();

            //提示操作失败
            return $this->response->array([
                'code' => 400,
                'text' =>trans('monitor23.fails'),
                'result' => ''
            ]);
        }
        DB::commit();


        return $this->response->array([
            'code' => 0,
            'text' =>trans('monitor23.success'),
            'result' => ''
        ]);
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/trigger",
     *   tags={"监控管理"},
     *   summary="获取监控数据列表",
     *   description="
     *   获取监控数据列表
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': {
    'total': 1,
    'per_page': 10,
    'current_page': 1,
    'last_page': 1,
    'next_page_url': null,
    'prev_page_url': null,
    'from': 1,
    'to': 1,
    'data': [
    {
    '_id': {
    '$oid': '59e984ea85eb834c4fc86774'
    },
    'user_id': 1,
    'user_name': 'user001',
    'hall_name': 'csj',
    'hall_id': 1,
    'agent_name': 'agent001',
    'agent_id': 2,
    'rule_tag': 'M002',
    'user_real_value': '10000',
    'rule_value': '5000',
    'number': 2,
    'last_trigger_date': '2017-10-20 02:24:49',
    'remark': '人工造的数据，后期可能会调整结构或者字段',
    'create_date': '2017-10-20 02:24:49',
    'ip_str': '192.168.31.155',
    'is_send_email': 1,
    'pass': 1
    }
    ]
    }
    }
    ",
     *   operationId="trigger",
     *   @SWG\Parameter(
     *     in="header",
     *     name="Accept",
     *     type="string",
     *     description="http头信息",
     *     required=true,
     *     default="Accept:application/vnd.agent.v1+json"
     *   ),
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="locale",
     *     type="string",
     *     description="语言标识",
     *     required=true,
     *     default="Accept:application/vnd.agent.v1+json"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="page_num",
     *     type="string",
     *     description="分页条数",
     *     required=true,
     *     default="10"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="tag",
     *     type="string",
     *     description="具体监控项标识符",
     *     required=true,
     *     default="M002"
     *   ),
     *  @SWG\Parameter(
     *     in="formData",
     *     name="token",
     *     type="string",
     *     description="token",
     *     required=true,
     *     default="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vYWdlbnQuZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNTA4NDY1NTUzLCJleHAiOjE1MDg2ODE1NTMsIm5iZiI6MTUwODQ2NTU1MywianRpIjoiaUxUcXd1VVhYZUcybzFuOSIsInN1YiI6NDY1fQ.ojgS4y1org7pjR3_h1_Jd2BWONBo2ruNrrjPP9bw4fY"
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function  getLog(Request $request)
    {
        $tag = $request->input('tag');
        $is_page = (int) $request->input('is_page', 1);
        $page_num = (int) $request->input('page_num', env('PAGE_NUM', 10));
        if(!$tag)
        {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('monitor23.invalid_error'),
                'result' => ''
            ]);
        }
        
        //只获取当天的记录
        $start_date = new UTCDateTime(strtotime(date("Y-m-d",time())) * 1000);
        $end_date = new UTCDateTime(strtotime(date("Y-m-d",time())." 23:59:59") * 1000);
        $obj = DB::connection('mongodb')->table('trigger_log')->select('*');
        $obj->where(['rule_tag'=>$tag]);
        $obj->where(['pass'=>1]);
        $obj->where(['hall_id'=>$this->agentId]);
        $obj->where('create_date','>=',$start_date);
        $obj->where('create_date','<=',$end_date);
        $obj->orderBy('create_date','desc');

        if( $is_page ) {
            $data = $obj->paginate($page_num)->toArray();
        } else {
            $data = $obj->get()->toArray();
        }
        //进行时间格式的转换
        if($data['data'])
        {
            foreach ($data['data'] as $k=>&$v)
            {
                $v['create_date'] = date("Y-m-d H:i:s",$v['create_date']->__toString()/1000);
                $v['last_trigger_date'] = date("Y-m-d H:i:s",$v['last_trigger_date']->__toString()/1000);
            }
        }

        return $this->response->array([
            'code' => 0,
            'text' => trans('monitor23.success'),
            'result' =>  $is_page ? $data : ['data' => $data],
        ]);
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/push/list",
     *   tags={"监控管理"},
     *   summary="查看报警记录列表",
     *   description="
     *   查看报警记录列表
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': {
    'total': 1,
    'per_page': 10,
    'current_page': 1,
    'last_page': 1,
    'next_page_url': null,
    'prev_page_url': null,
    'from': 1,
    'to': 1,
    'data': [
    {
    '_id': {
    '$oid': '59e987ba85eb834c4fc86b2e'
    },
    'rule_tag': 'M001',
    'pass': 1,
    'user_name': 'A001',
    'hall_id': 1,
    'hall_name': 'HALL_001',
    'agent_id': 2,
    'agent_name': 'agent_009',
    'remark': '手动造的数据',
    'create_date': '2017-10-20 02:24:49'
    }
    ]
    }
    }
    ",
     *   operationId="trigger",
     *   @SWG\Parameter(
     *     in="header",
     *     name="Accept",
     *     type="string",
     *     description="http头信息",
     *     required=true,
     *     default="Accept:application/vnd.agent.v1+json"
     *   ),
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="locale",
     *     type="string",
     *     description="语言标识",
     *     required=true,
     *     default="Accept:application/vnd.agent.v1+json"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="page_num",
     *     type="string",
     *     description="分页条数",
     *     required=true,
     *     default="10"
     *   ),
     *  @SWG\Parameter(
     *     in="formData",
     *     name="token",
     *     type="string",
     *     description="token",
     *     required=true,
     *     default="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vYWdlbnQuZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNTA4NDY1NTUzLCJleHAiOjE1MDg2ODE1NTMsIm5iZiI6MTUwODQ2NTU1MywianRpIjoiaUxUcXd1VVhYZUcybzFuOSIsInN1YiI6NDY1fQ.ojgS4y1org7pjR3_h1_Jd2BWONBo2ruNrrjPP9bw4fY"
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function getPushLog(Request $request)
    {
        $is_page = (int) $request->input('is_page', 1);
        $page_num = (int) $request->input('page_num', env('PAGE_NUM', 10));

        //只获取当天的记录
        $start_date = new UTCDateTime(strtotime(date("Y-m-d",time())) * 1000);
        $end_date = new UTCDateTime(strtotime(date("Y-m-d",time())." 23:59:59") * 1000);
        $obj = DB::connection('mongodb')->table('alarm_push_log')->select();
        $obj->where('create_date','>=',$start_date);
        $obj->where('create_date','<=',$end_date);
        $obj->where(['pass'=>1]);
        $obj->where(['hall_id'=>$this->agentId]);
        $obj->orderBy('create_date','desc');

        if( $is_page ) {
            $data = $obj->paginate($page_num)->toArray();
        } else {
            $data = $obj->get()->toArray();
        }
        foreach ($data['data'] as $k=>&$v)
        {
            $v['create_date'] = date("Y-m-d H:i:s",$v['create_date']->__toString()/1000);
        }
        return $this->response->array([
            'code' => 0,
            'text' => trans('monitor23.success'),
            'result' =>  $is_page ? $data : ['data' => $data],
        ]);
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/alarm/list",
     *   tags={"监控管理"},
     *   summary="获取报警账号列表操作",
     *   description="
     *   获取报警账号列表操作
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': {
    'total': 3,
    'per_page': 10,
    'current_page': 1,
    'last_page': 1,
    'next_page_url': null,
    'prev_page_url': null,
    'from': 1,
    'to': 3,
    'data': [
    {
    'id': 11,
    'hall_id': 1,
    'mobile': '123214569851',
    'email': 'sadsa@qq.com',
    'last_date': '2017-10-18 04:49:33'
    },
    ]
    }
    }
    ",
     *   operationId="trigger",
     *   @SWG\Parameter(
     *     in="header",
     *     name="Accept",
     *     type="string",
     *     description="http头信息",
     *     required=true,
     *     default="Accept:application/vnd.agent.v1+json"
     *   ),
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="locale",
     *     type="string",
     *     description="语言标识",
     *     required=true,
     *     default="Accept:application/vnd.agent.v1+json"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="page_num",
     *     type="string",
     *     description="分页条数",
     *     required=true,
     *     default="10"
     *   ),
     *  @SWG\Parameter(
     *     in="formData",
     *     name="token",
     *     type="string",
     *     description="token",
     *     required=true,
     *     default="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vYWdlbnQuZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNTA4NDY1NTUzLCJleHAiOjE1MDg2ODE1NTMsIm5iZiI6MTUwODQ2NTU1MywianRpIjoiaUxUcXd1VVhYZUcybzFuOSIsInN1YiI6NDY1fQ.ojgS4y1org7pjR3_h1_Jd2BWONBo2ruNrrjPP9bw4fY"
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function alarmList(Request $request)
    {
        $is_page = (int) $request->input('is_page', 1);
        $page_num = (int) $request->input('page_num', env('PAGE_NUM', 10));
        $obj = DB::table('sys_alarm_account')->where(['hall_id'=>$this->agentId])->select('*');

        if( $is_page ) {
            $data = $obj->paginate($page_num);
        } else {
            $data = $obj->get();
        }

        return $this->response->array([
            'code' => 0,
            'text' => trans('monitor23.success'),
            'result' =>  $is_page ? $data : ['data' => $data],
        ]);
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Post(
     *   path="/alarm",
     *   tags={"监控管理"},
     *   summary="添加报警账号操作",
     *   description="
     *   添加报警账号操作
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
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
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="locale",
     *     type="string",
     *     description="语言标识",
     *     required=true,
     *     default="en"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="mobile",
     *     type="string",
     *     description="手机号码",
     *     required=true,
     *     default="13525588965"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="email",
     *     type="string",
     *     description="邮箱",
     *     required=true,
     *     default="123@qq.com"
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
    public function addAlarm(Request $request)
    {
        $id = $request->input('id');
        $data['mobile'] = $request->input('mobile');
        $data['email'] = $request->input('email');
        $data['hall_id'] = $this->agentId;
        $data['last_date'] = date('Y-m-d H:i:s',time());

        $message = [
            'mobile.required' => trans('monitor23.mobile.required'),
            'email.required' => trans('monitor23.email.required'),
            'email.email' => trans('monitor23.email.email'),
        ];
        $validator = \Validator::make($request->input(), [
            'mobile' => 'required',
            'email' => 'required|email',
        ],$message);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        //进行正常添加操作
        $res = DB::table('sys_alarm_account')->insert($data);
        if(!$res)
        {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('monitor23.fails'),
                'result' => ''
            ]);
        }

        return $this->response->array([
            'code' => 0,
            'text' =>trans('monitor23.success'),
            'result' => ''
        ]);
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/alarm",
     *   tags={"监控管理"},
     *   summary="编辑报警账号时获取信息",
     *   description="
     *   编辑报警账号时获取信息
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': {
    'data': {
    'id': 14,
    'hall_id': 1,
    'mobile': '13562255984',
    'email': '22@qq.com',
    'last_date': '2017-10-20 01:39:40'
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
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="locale",
     *     type="string",
     *     description="语言标识",
     *     required=true,
     *     default="en"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="id",
     *     type="number",
     *     description="数据ID",
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
    public function getAlarmInfo(Request $request)
    {
        $id = $request->input('id');
        if(!$id)
        {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('monitor23.invalid_error'),
                'result' => ''
            ]);
        }
        //判断记录是否存在
        $find = DB::table('sys_alarm_account')->where(['id'=>$id,'hall_id'=>$this->agentId])->first();
        if(!$find)
        {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('monitor23.not_exists'),
                'result' =>""
            ]);
        }

        //返回信息操作
        return $this->response->array([
            'code' => 0,
            'text' =>trans('monitor23.success'),
            'result' => ['data' => $find],
        ]);

    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Put(
     *   path="/alarm",
     *   tags={"监控管理"},
     *   summary="修改报警账号操作",
     *   description="
     *   修改报警账号操作
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
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
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="locale",
     *     type="string",
     *     description="语言标识",
     *     required=true,
     *     default="en"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="id",
     *     type="number",
     *     description="数据ID",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="mobile",
     *     type="string",
     *     description="手机号码",
     *     required=true,
     *     default="13525588965"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="email",
     *     type="string",
     *     description="邮箱",
     *     required=true,
     *     default="123@qq.com"
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
    public function updateAlarm(Request $request)
    {
        $id = $request->input('id');
        $data['mobile'] = $request->input('mobile');
        $data['email'] = $request->input('email');
        $data['hall_id'] = $this->agentId;
        $data['last_date'] = date('Y-m-d H:i:s',time());

        $message = [
            'mobile.required' => trans('monitor23.mobile.required'),
            'email.required' => trans('monitor23.email.required'),
            'email.email' => trans('monitor23.email.email'),
        ];
        $validator = \Validator::make($request->input(), [
            'mobile' => 'required',
            'email' => 'required|email',
        ],$message);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        //判断记录是否存在
        $find = DB::table('sys_alarm_account')->where(['id'=>$id,'hall_id'=>$this->agentId])->first();
        if(!$find)
        {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('monitor23.not_exists'),
                'result' =>""
            ]);
        }

        //进行正常修改操作
        $res = DB::table('sys_alarm_account')->where(['id'=>$id,'hall_id'=>$this->agentId])->update($data);
        if(!$res)
        {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('monitor23.fails'),
                'result' => ''
            ]);
        }

        return $this->response->array([
            'code' => 0,
            'text' =>trans('monitor23.success'),
            'result' => ''
        ]);

    }

    /**
    /**
     * consumes={"multipart/form-data"},
     * @SWG\Delete(
     *   path="/alarm",
     *   tags={"监控管理"},
     *   summary="删除报警账号操作",
     *   description="
     *   删除报警账号操作
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
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
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="locale",
     *     type="string",
     *     description="语言标识",
     *     required=true,
     *     default="en"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="id",
     *     type="number",
     *     description="数据ID",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="mobile",
     *     type="string",
     *     description="手机号码",
     *     required=true,
     *     default="13525588965"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="email",
     *     type="string",
     *     description="邮箱",
     *     required=true,
     *     default="123@qq.com"
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
    public function deleteAlarm(Request $request)
    {
        $id = $request->input('id');
        if(!$id)
        {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('monitor23.invalid_error'),
                'result' => ''
            ]);
        }

        //判断记录是否存在
        $find = DB::table('sys_alarm_account')->where(['id'=>$id,'hall_id'=>$this->agentId])->first();
        if(!$find)
        {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('monitor23.not_exists'),
                'result' =>""
            ]);
        }

        //进行正常删除操作
        $de = DB::table('sys_alarm_account')->where(['id'=>$id,'hall_id'=>$this->agentId])->delete();
        if(!$de)
        {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('monitor23.fails'),
                'result' => ''
            ]);
        }

        return $this->response->array([
            'code' => 0,
            'text' =>trans('monitor23.success'),
            'result' => ''
        ]);
    }

}