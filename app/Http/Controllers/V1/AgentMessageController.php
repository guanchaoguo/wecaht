<?php
/**
 * 厅主公告控制器
 * User: chensongjian
 * Date: 2017/10/19
 * Time: 10:10
 */

namespace App\Http\Controllers\V1;

use Illuminate\Support\Facades\DB;

class AgentMessageController extends BaseController
{

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/agent/message",
     *   tags={"厅主公告"},
     *   summary="获取厅主公告",
     *   description="
     *   成功返回字段说明
    {'code': 0,
    'text': '操作成功',
    'result': {
        'data' : [
            {'message': '公告内容'}
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
    public function index()
    {
        $now_time = date('Y-m-d H:i:s');
        $data = DB::table('hall_message')->select('message')->where('start_date', '<=', $now_time)->where('end_date', '>=', $now_time)->orderby('create_date', 'desc')->get();

        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => [
                'data' => $data
            ],
        ]);
    }
}