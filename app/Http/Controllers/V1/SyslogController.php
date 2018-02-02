<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkj.com
 * Date: 2017/4/10
 * Time: 14:46
 * 系统日志控制器
 */

namespace App\Http\Controllers\V1;

use App\Models\Agent;
use App\Models\Apilog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SyslogController extends BaseController
{
    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="syslog",
     *   tags={"日志管理"},
     *   summary="厅主日志操作 列表",
     *   description="
     *   成功返回字段说明
    {
    'code': 0,
    'text': 'delivery.success',
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
    '$oid': '595c9a4e3c1a2115e4001438'
    },
    'action_name': ' 编辑保持账户权限信息',
    'user_id': 454,
    'action_user': 'gcg',
    'action_desc': ' 编辑子账户权限信息; 名称gaucnhaoguo ID458',
    'action_passivity': '代理商账号表',
    'action_date': '2017-07-05 03:50:38',
    'ip_info': '127.0.0.1'
    }
    ]
    }
    }",
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
     *     name="action_user",
     *     type="string",
     *     description="操作者",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="action_passivity",
     *     type="string",
     *     description="被操作对象",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="action_passivity",
     *     type="string",
     *     description="被操作对象描述",
     *     required=false,
     *     default=""
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
        $action_user = $request->input('action_user');
        $action_passivity = $request->input('action_passivity');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $page_num = (int)$request->input('page_num',10);
        $is_page = $request->input('is_page', 1);
        $db = DB::connection('mongodb')->collection('agent_operation_log');

        //查询当前日志为用户只能查看子账号 代理和自己操作日志
        if(!empty($action_user))
        {
            $db->where('action_user','like','%'.$action_user.'%');
        }else{
            // 查询出该当前厅主的信息
            $user = \Illuminate\Support\Facades\Auth::user();

            //查询出厅主子账号 代理信息
            $userNameList = Agent::where(['parent_id'=>$user->id])->pluck('user_name')->toArray();

            // 加入厅主用户名
            array_push($userNameList,$user->user_name);

            //去重用户名
            $userName = array_unique($userNameList);

            // 查询用户名
            $db->whereIn('action_user', $userName);
        }

        if(!empty($action_passivity))
        {
            $db->where('action_passivity','like','%'.$action_passivity.'%');
        }

        if(!empty($start_date))
        {
            $db->where('action_date','>=',$start_date);
        }

        if(!empty($end_date) && strtotime($start_date) < strtotime($end_date))
        {
            $db->where('action_date','<=',$end_date);
        }

        $db->orderBy('action_date','desc');
        if(!$is_page) {
            $res = $db->get()->toArray();
        } else {
            $res = $db->paginate($page_num)->toArray();
        }

        if(!$res['data'])
        {
            return  $this->response()->array([
                'code'          => 400,
                'text'          => trans('delivery.empty_list'),
                'result'        => ''
            ]);
        }

        return  $this->response()->array([
            'code'          => 0,
            'text'          => trans('delivery.success'),
            'result'        => $res
        ]);
    }

}