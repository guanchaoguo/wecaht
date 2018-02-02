<?php
/**
 * 游戏厅主文案LOGO.
 * User: chensongjian
 * Date: 2017/4/13
 * Time: 10:03
 */

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Models\Agent;
use App\Models\GamePlatformLogo;


class GamePlatformLogoController extends BaseController
{

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/copywriter/logo",
     *   tags={"文案管理"},
     *   summary="文案-logo 列表",
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
    'id': 2,//id
    'p_id': 1,//厅主id
    'p_name': '',//厅主名称
    'title': 'test_title',//标题
    'label': 1,//所属平台,0为PC，1为手机横版，2为手机竖版
    'logo': 'images/12121.jpg',//图片地址
    'add_date': '2017-04-17 14:44:39',//添加时间
    'update_date': '2017-04-17 14:44:39',//修改时间
    'status': 0,//审核状态，0：未审核，1：已审核，2：审核不通过
    'is_use': 0,//启用状态：0 未使用，1已使用
    'full_logo': 'http://192.168.31.230:8000/images/12121.jpg',//全路径活动图片地址
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

        $is_page = (int)$request->input('is_page', 1);
        $page    = (int)$request->input('page', 1);
        $page_num = (int)$request->input('page_num', env('PAGE_NUM', 10));

        $where = [
            'p_id' => $this->agentId
        ];
        $db = GamePlatformLogo::select('*',\DB::raw('CONCAT("'.env('IMAGE_HOST').'", logo) AS full_logo'))->where($where);

        $db->orderby('is_use', 'desc');
        $db->orderby('label', 'asc');
        $db->orderby('add_date', 'desc');

        if( $is_page ) {
            $data = $db->paginate($page_num);
        } else {
            $data = $db->get();
        }

        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' =>  $is_page ? $data : ['data'=>$data],
        ]);
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Post(
     *   path="/copywriter/logo",
     *   tags={"文案管理"},
     *   summary="文案-logo 添加",
     *   description="
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': ''
    }",
     *   operationId="store",
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
     *     description="标题",
     *     required=true,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="label",
     *     type="integer",
     *     description="所属平台,0为PC，1为手机横版，2为手机竖版 ",
     *     required=true,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="logo",
     *     type="string",
     *     description="图片地址 格式：images/12121.jpg",
     *     required=true,
     *     default=""
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function store(Request $request)
    {

        if($this->agentInfo['grade_id'] == 2) {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.grade_id_error'),
                'result' => '',
            ]);
        }

        $title = $request->input('title');
        $label = (int)$request->input('label');
        $logo = $request->input('logo');

        if( ! $agent = Agent::where(['id'=>$this->agentId,'grade_id' =>1,'is_hall_sub' => 0])->first() ){
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.agent_not_exist'),
                'result' => '',
            ]);
        }

        /*if( GamePlatformLogo::where('p_id',$this->agentId)->first() ) {
            return $this->response->array([
                'code'=>400,
                'text'=>trans('agent.hall_has_data'),
                'result'=>'',
            ]);
        }*/

        $message = [
            'logo.required' => trans('copywriter.logo_required'),
            'label.required' => trans('copywriter.label.required'),
            'label.in' => trans('copywriter.label.in'),
        ];
        $validator = \Validator::make($request->input(), [
            'title' => 'required',
            'label' => 'required|in:0,1,2',
            'logo' => 'required',
        ],$message);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        $attributes = [
            'p_id' => $this->agentId,
            'p_name' => $this->agentInfo['user_name'],
            'title' => $title,
            'label' => $label,
            'logo' => $logo,
        ];
        $re = GamePlatformLogo::create($attributes);

        if( $re ) {
            // 添加操作日志
            @addLog([
                'action_name'=> '添加活动logo',
                'action_desc'=> "添加活动logoID:{$re->id}",
                'action_passivity'=>'游戏厅主文案LOGO信息表'
            ]);

            return $this->response->array([
                'code' => 0,
                'text' =>trans('agent.success'),
                'result' => '',
            ]);

        } else {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.fails'),
                'result' => '',
            ]);
        }
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/copywriter/logo/{id}",
     *   tags={"文案管理"},
     *   summary="文案-logo 详情",
     *   description="
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': {
    'id': 2,//活动id
    'p_id': 1,//厅主id
    'p_name': '',//厅主名称
    'title': 'test_title',//标题
    'label': 1,//所属平台,0为PC，1为手机横版，2为手机竖版
    'logo': 'images/12121.jpg',//图片地址
    'add_date': '2017-04-17 14:44:39',//添加时间
    'update_date': '2017-04-17 14:44:39',//修改时间
    'full_logo': 'http://192.168.31.230:8000/images/12121.jpg',//全路径活动图片地址
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

        if($this->agentInfo['grade_id'] == 2) {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.grade_id_error'),
                'result' => '',
            ]);
        }

        $data = GamePlatformLogo::where(['p_id'=>$this->agentId,'id'=> $id])->first();
        if( ! $data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.data_not_exist'),
                'result' => '',
            ]);
        }
        $data->full_logo = env('IMAGE_HOST').$data->logo;
        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => $data,
        ]);
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Put(
     *   path="/copywriter/logo/{id}",
     *   tags={"文案管理"},
     *   summary="文案-logo 编辑",
     *   description="
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': ''
    }",
     *   operationId="store",
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
     *     description="标题",
     *     required=true,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="label",
     *     type="integer",
     *     description="所属平台,0为PC，1为手机横版，2为手机竖版 ",
     *     required=true,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="logo",
     *     type="string",
     *     description="图片地址 格式：images/12121.jpg",
     *     required=true,
     *     default=""
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function update(Request $request, int $id)
    {

        if($this->agentInfo['grade_id'] == 2) {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.grade_id_error'),
                'result' => '',
            ]);
        }
        $title =$request->input('title');
        $label = (int)$request->input('label');
        $logo = $request->input('logo');

        $data = GamePlatformLogo::where(['p_id'=>$this->agentId,'id'=> $id])->first();
        if( ! $data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.data_not_exist'),
                'result' => '',
            ]);
        }

        //已审核的数据，不能编辑
        if( $data->status == 1 ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.data_has_review'),
                'result' => '',
            ]);
        }

        if( ! $agent = Agent::where(['id'=>$this->agentId,'grade_id' =>1,'is_hall_sub' => 0])->first() ){
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.agent_not_exist'),
                'result' => '',
            ]);
        }

        $message = [
            'logo.required' => trans('copywriter.logo_required'),
            'label.required' => trans('copywriter.label.required'),
            'label.in' => trans('copywriter.label.in'),
        ];
        $validator = \Validator::make($request->input(), [
            'title' => 'required',
            'label' => 'required|in:0,1,2',
            'logo' => 'required',
        ],$message);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        $attributes = [
            'title' => $title,
            'label' => $label,
            'logo' => $logo,
            'status' => 0,//编辑后重新审核
        ];
        $re = GamePlatformLogo::where('id', $id)->update($attributes);
        if( $re !== false ) {
            // 添加操作日志
            @addLog([
                'action_name'=> '修改活动logo',
                'action_desc'=> "修改活动logoID:{$id}",
                'action_passivity'=>'游戏厅主文案LOGO信息表'
            ]);

            return $this->response->array([
                'code' => 0,
                'text' =>trans('agent.success'),
                'result' => '',
            ]);
        } else {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.save_fails'),
                'result' => '',
            ]);
        }
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Delete(
     *   path="/copywriter/logo/{id}",
     *   tags={"文案管理"},
     *   summary="文案-logo 删除",
     *   description="
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': ''
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
    public function delete(Request $request, int $id)
    {
        if($this->agentInfo['grade_id'] == 2) {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.grade_id_error'),
                'result' => '',
            ]);
        }
        $data = GamePlatformLogo::where(['p_id'=>$this->agentId,'id'=> $id])->first();
        if( ! $data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.data_not_exist'),
                'result' => '',
            ]);
        }

        $re = GamePlatformLogo::destroy($id);
        if( !$re ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.fails'),
                'result' => '',
            ]);
        }

        // 添加操作日志
        @addLog([
            'action_name'=> '删除活动logo',
            'action_desc'=> "删除活动logoID:{$id}",
            'action_passivity'=>'游戏厅主文案LOGO信息表'
        ]);

        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => '',
        ]);
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Patch(
     *   path="/copywriter/logo/{id}/sort",
     *   tags={"文案管理"},
     *   summary="文案-logo 排序",
     *   description="
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': ''
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
     *     name="sort",
     *     type="string",
     *     description="排序 数字越小越靠前",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function sort(Request $request, int $id)
    {
        $sort = $request->input('sort');

        if($this->agentInfo['grade_id'] == 2) {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.grade_id_error'),
                'result' => '',
            ]);
        }
        $data = GamePlatformLogo::where(['p_id'=>$this->agentId,'id'=> $id])->first();;
        if( ! $data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.data_not_exist'),
                'result' => '',
            ]);
        }

        $re = GamePlatformLogo::where('id',$id)->update(['sort' => $sort]);

        if($re !== false) {
            // 添加操作日志
            @addLog([
                'action_name'=> '排序活动logo',
                'action_desc'=> "排序活动logoID:{$id}",
                'action_passivity'=>'游戏厅主文案LOGO信息表'
            ]);

            return $this->response->array([
                'code' => 0,
                'text' => trans('agent.success'),
                'result' => '',
            ]);
        }
        return $this->response->array([
            'code' => 400,
            'text' => trans('agent.fails'),
            'result' => '',
        ]);
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Patch(
     *   path="/copywriter/logo/{id}/isUse",
     *   tags={"文案管理"},
     *   summary="文案-logo 启用&禁用",
     *   description="
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': ''
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
     *     name="is_use",
     *     type="string",
     *     description="是否启用 1：启用 ，0：禁用",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function isUse(Request $request, int $id) {

        $is_use = $request->input('is_use');

        if($this->agentInfo['grade_id'] == 2) {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.grade_id_error'),
                'result' => '',
            ]);
        }

        $data = GamePlatformLogo::where(['p_id'=>$this->agentId,'id'=> $id])->first();;
        if( ! $data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.data_not_exist'),
                'result' => '',
            ]);
        }

        if( ! in_array($is_use, [0,1]) ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.param_error'),
                'result' => '',
            ]);
        }

        //每个平台logo只能有一条数据为启用，若要启用，先把之前的已启用状态置为0
        $is_use && GamePlatformLogo::where('p_id', $this->agentId)->where('is_use', 1)->where('label', $data['label'])->update(['is_use' => 0]);

        $re = GamePlatformLogo::where('id', $id)->update(['is_use' => $is_use]);
        if( $re !== false ) {
            // 添加操作日志
            $option = $is_use == 1 ? '启动':'禁用';
            @addLog([
                'action_name'=> $option.'活动logo',
                'action_desc'=> $option."活动logoID:{$id}",
                'action_passivity'=>'游戏厅主文案LOGO信息表'
            ]);

            return $this->response->array([
                'code' => 0,
                'text' =>trans('agent.success'),
                'result' => '',
            ]);
        } else {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.save_fails'),
                'result' => '',
            ]);
        }


    }
}