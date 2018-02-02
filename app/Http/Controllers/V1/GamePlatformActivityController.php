<?php
/**
 * 文案活动
 * User: chensongjian
 * Date: 2017/4/17
 * Time: 13:23
 */

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Models\Agent;
use App\Models\GamePlatformActivity;

class GamePlatformActivityController extends BaseController
{
    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/copywriter/activity",
     *   tags={"文案管理"},
     *   summary="文案-活动 列表",
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
    'id': 2,//活动id
    'p_id': 1,//厅主id
    'title': 'test_title',//活动标题
    'play_type': 0,//展现方式，0为弹框形式，1为其他
    'label': 1,//所属平台,0为PC，1为手机横版，2为手机竖版
    'play_place': 1,//展示位置，0为页面居中方式，1为其他
    'start_date': '2017-04-13 15:16:18',//活动开始时间
    'end_date': '2017-04-13 15:16:19',//活动结束时间
    'img': 'images/12121.jpg',//活动图片地址
    'status': 0,//审核状态，0：未审核，1：已审核，2：审核不通过
    'add_date': '2017-04-17 14:44:39',//添加时间
    'update_date': '2017-04-17 14:44:39',//修改时间
    'full_img': 'http://192.168.31.230:8000/images/12121.jpg',//全路径活动图片地址
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
        $page    = (int) $request->input('page', 1);
        $page_num = (int) $request->input('page_num', env('PAGE_NUM', 10));
        $where = [
            'p_id' => $this->agentId
        ];

        $db = GamePlatformActivity::select('*',\DB::raw('CONCAT("'.env('IMAGE_HOST').'", img) AS full_img'))->where($where);
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
     *   path="/copywriter/activity",
     *   tags={"文案管理"},
     *   summary="文案-活动 添加",
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
     *     description="LOGO描述信息",
     *     required=true,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="play_type",
     *     type="integer",
     *     description="轮播方式，0为从左到右，1为从右到左",
     *     required=true,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="play_place",
     *     type="integer",
     *     description="展示位置，0为页面居中方式，1为其他",
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
     *     name="start_date",
     *     type="string",
     *     description="活动开始时间 2017-04-13 15:16:19 ",
     *     required=true,
     *     default="2017-04-13 15:16:19"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="end_date",
     *     type="string",
     *     description="活动结束时间 2017-04-13 15:16:20",
     *     required=true,
     *     default="2017-04-13 15:16:19"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="img",
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
        $play_type = (int) $request->input('play_type');
        $play_place = (int) $request->input('play_place');
        $label = (int) $request->input('label');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $img = $request->input('img');

        if( ! $agent = Agent::where(['id'=>$this->agentId,'grade_id' =>1,'is_hall_sub' => 0])->first() ){
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.agent_not_exist'),
                'result' => '',
            ]);
        }

        if( GamePlatformActivity::where('p_id',$this->agentId)->first() ) {
            return $this->response->array([
                'code'=>400,
                'text'=>trans('agent.hall_has_data'),
                'result'=>'',
            ]);
        }

        $message = [
            'img.required' => trans('copywriter.logo_required'),
            'label.required' => trans('copywriter.label.required'),
            'label.in' => trans('copywriter.label.in'),
            'play_type.required' => trans('copywriter.play_type.required'),
            'play_type.in' => trans('copywriter.play_type.in'),
            'play_place.required' => trans('copywriter.play_place.required'),
            'play_place.in' => trans('copywriter.play_place.in'),
            'start_date.required' => trans('copywriter.start_date.required'),
            'end_date.required' => trans('copywriter.end_date.required'),
        ];
        $validator = \Validator::make($request->input(), [
            'title' => 'required',
            'play_type' => 'required|in:0,1',
            'label' => 'required|in:0,1,2',
            'play_place' => 'required|in:0,1',
            'start_date' => 'required',
            'end_date' => 'required',
            'img' => 'required',
        ],$message);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        if($start_date >= $end_date) {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('copywriter.start_date.gt'),
                'result'=>'',
            ]);
        }

        $attributes = [
            'p_id' => $this->agentId,
            'title' => $title,
            'play_type' => $play_type,
            'play_place' => $play_place,
            'label' => $label,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'img' => $img,
        ];

        $re = GamePlatformActivity::create($attributes);

        if( $re ) {
            // 添加操作日志
            @addLog([
                'action_name'=>'添加活动文案',
                'action_desc'=>"添加文案的ID:{$re->id}",
                'action_passivity'=>'厅主游戏活动管理表'
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
     *   path="/copywriter/activity/{id}",
     *   tags={"文案管理"},
     *   summary="文案-活动 详情",
     *   description="
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': {
    'id': 2,//活动id
    'p_id': 1,//厅主id
    'title': 'test_title',//活动标题
    'play_type': 0,//展现方式，0为弹框形式，1为其他
    'label': 1,//所属平台,0为PC，1为手机横版，2为手机竖版
    'play_place': 1,//展示位置，0为页面居中方式，1为其他
    'start_date': '2017-04-13 15:16:18',//活动开始时间
    'end_date': '2017-04-13 15:16:19',//活动结束时间
    'img': 'images/12121.jpg',//活动图片地址
    'status': 0,//审核状态，0：未审核，1：已审核，2：审核不通过
    'add_date': '2017-04-17 14:44:39',//添加时间
    'update_date': '2017-04-17 14:44:39',//修改时间
    'full_img': 'http://192.168.31.230:8000/images/12121.jpg',//全路径活动图片地址
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
        $data = GamePlatformActivity::where(['p_id'=>$this->agentId,'id'=> $id])->first();
        if( ! $data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.data_not_exist'),
                'result' => '',
            ]);
        }
        $data->full_img = env('IMAGE_HOST').$data->img;
        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => $data,
        ]);
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Put(
     *   path="/copywriter/activity/{id}",
     *   tags={"文案管理"},
     *   summary="文案-活动 编辑",
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
     *     description="LOGO描述信息",
     *     required=true,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="play_type",
     *     type="integer",
     *     description="轮播方式，0为从左到右，1为从右到左",
     *     required=true,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="play_place",
     *     type="integer",
     *     description="展示位置，0为页面居中方式，1为其他",
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
     *     name="start_date",
     *     type="string",
     *     description="活动开始时间 2017-04-13 15:16:19 ",
     *     required=true,
     *     default="2017-04-13 15:16:19"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="end_date",
     *     type="string",
     *     description="活动结束时间 2017-04-13 15:16:20",
     *     required=true,
     *     default="2017-04-13 15:16:19"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="img",
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
        $title = $request->input('title');
        $play_type = (int) $request->input('play_type');
        $label = (int) $request->input('label');
        $play_place = (int) $request->input('play_place');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $img = $request->input('img');

        $data = GamePlatformActivity::where(['p_id'=>$this->agentId,'id'=> $id])->first();
        if( ! $data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.data_not_exist'),
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
            'img.required' => trans('copywriter.logo_required'),
            'label.required' => trans('copywriter.label.required'),
            'label.in' => trans('copywriter.label.in'),
            'play_type.required' => trans('copywriter.play_type.required'),
            'play_type.in' => trans('copywriter.play_type.in'),
            'play_place.required' => trans('copywriter.play_place.required'),
            'play_place.in' => trans('copywriter.play_place.in'),
            'start_date.required' => trans('copywriter.start_date.required'),
            'end_date.required' => trans('copywriter.end_date.required'),
        ];
        $validator = \Validator::make($request->input(), [
            'title' => 'required',
            'play_type' => 'required|in:0,1',
            'label' => 'required|in:0,1,2',
            'play_place' => 'required|in:0,1',
            'start_date' => 'required',
            'end_date' => 'required',
            'img' => 'required',
        ],$message);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        if($start_date >= $end_date) {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('copywriter.start_date.gt'),
                'result'=>'',
            ]);
        }
        $attributes = [
            'title' => $title,
            'play_type' => $play_type,
            'play_place' => $play_place,
            'label' => $label,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'img' => $img,
            'status' => 0,//编辑后重新审核
        ];

        $re = GamePlatformActivity::where('id', $id)->update($attributes);

        if( $re !== false ) {
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
     *   path="/copywriter/activity/{id}",
     *   tags={"文案管理"},
     *   summary="文案-活动 删除",
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
        $data = GamePlatformActivity::where(['p_id'=>$this->agentId,'id'=> $id])->first();
        if( ! $data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.data_not_exist'),
                'result' => '',
            ]);
        }

        $re = GamePlatformActivity::destroy($id);
        if( !$re ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.fails'),
                'result' => '',
            ]);
        }

        // 添加操作日志
        @addLog([
            'action_name'=>'删除文案操作',
            'action_desc'=>"删除的ID:{$id}",
            'action_passivity'=>'代理商账号表'
        ]);

        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => '',
        ]);
    }
}