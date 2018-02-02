<?php
/**
 * 文档管理控制器
 * User: chensongjian
 * Date: 2017/6/19
 * Time: 14:23
 */

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Models\GamePlatformDocument;

class DocumentController extends BaseController
{


    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/document",
     *   tags={"文档管理"},
     *   summary="文档列表",
     *   description="
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
    'id': 1,//id
    'title': '',//文档名称
    'size': "",//文档大小
    'path': "",//文档保存相对路径
    'desc': "",//文档备注描述
    'add_time': '2017-04-17 11:13:41',//添加时间
    'full_path': 'http://192.168.31.230:8000/./upload/doc/2017/06/19/23334343.docx',//文档全路径（下载路径）
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

        if($this->agentInfo['grade_id'] == 2) {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.grade_id_error'),
                'result' => '',
            ]);
        }

        $is_page = (int) $request->input('is_page', 1);
        $page_num = (int) $request->input('page_num', env('PAGE_NUM', 10));

        $obj = GamePlatformDocument::select('*', \DB::raw('CONCAT("'.env('IMAGE_HOST').'", path) AS full_path'));
        $obj->orderby('add_time', 'desc');

        if( $is_page ) {
            $data = $obj->paginate($page_num);
        } else {
            $data = $obj->get();
        }

        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' =>  $is_page ? $data : ['data' => $data],
        ]);
    }

}