<?php
/**
 * 游戏风格模板控制器
 * User: chensongjian
 * Date: 2017/4/11
 * Time: 10:31
 */

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Models\Agent;
use App\Models\GameTemplate;
use App\Models\GameTemplateImages;
class GameTemplateController extends BaseController
{

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/gameTemplate",
     *   tags={"模板管理"},
     *   summary="风格模板列表",
     *   description="
     *   成功返回字段说明
        {
        'code': 0,
        'text': '操作成功',
        'result': {
        'total': 1,
        'per_page': '10',
        'current_page': 1,
        'last_page': 1,
        'next_page_url': null,
        'prev_page_url': null,
        'from': 1,
        'to': 1,
        'data': [
        {
        'id': 2,//模板id
        'title': '模板test',//模板标题
        'desc': '这是描述',//模板说明
        'label': 0,//所属平台：0为PC，1为手机横版，2为手机竖版
        'code': 'bbb',//模板代码
        'state': 0,'//模板启用状态，0为未启用，1为启用，默认为0'
        'add_date': '2017-04-11 15:33:04'//添加时间
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
     *     name="title",
     *     type="string",
     *     description="模板标题",
     *     required=false,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="state",
     *     type="integer",
     *     description="模板启用状态，0为未启用，1为启用，默认为0",
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

        if( $this->agentInfo['grade_id'] == 2 ) {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.grade_id_error'),
                'result' => '',
            ]);

        }

        $title = $request->input('title');
        $state = $request->input('state');
        $page_num = $request->input('page_num', env('PAGE_NUM'));
        $page = $request->input('page', 1);
        $is_page = $request->input('is_page', 1);

        $db = GameTemplate::orderby('add_date', 'desc');

        if(isset($state) && $state !== '') {
            $db->where('state', $state);
        }

        if(isset($title) && !empty($title)) {
            $db->where('title', 'like', '%'.$title.'%');
        }
        if($is_page) {
            $data = $db->paginate($page_num);
        } else {
            $data['data'] = $db->get();
        }
        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => $data,
        ]);
    }



    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/gameTemplate/{id}",
     *   tags={"模板管理"},
     *   summary="风格模板详情",
     *   description="
     *   成功返回字段说明
        {
        'code': 0,
        'text': '操作成功',
        'result': {
        'id': 2,//模板id
        'title': '模板test',//模板标题
        'desc': '这是描述',//模板描述说明
        'label': 0,//所属平台,0为PC，1为手机横版，2为手机竖版
        'code': 'bbb',//模板代码
        'state': 0,//模板启用状态，0为未启用，1为启用，默认为0
        'add_date': '2017-04-11 15:33:04',//添加时间
        'images': [//模板图片
        {
        'img': 'http://platform.dev/images/2017-03-01-14-21-02-58b6684ecc36e.jpg'
        },
        {
        'img': 'http://platform.dev/images/2017-04-11-14-21-02-323dsds43434df.jpg'
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
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function show(Request $request, int $id)
    {

        if( $this->agentInfo['grade_id'] == 2 ) {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.grade_id_error'),
                'result' => '',
            ]);

        }
        $data = GameTemplate::find($id);
        if( !$data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('template.not_exist'),
                'result' => '',
            ]);
        }
        $data->images;
        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => $data,
        ]);
    }


}