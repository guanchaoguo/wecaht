<?php
/**
 * Created by PhpStorm.
 * User: chengkang
 * Date: 2017/2/8
 * Time: 17:38
 */
namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Models\RoomGameType;
use App\Models\RoomInfo;
use App\Models\RoomRules;
class RoomInfoController extends BaseController
{
    public function __construct()
    {
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/room",
     *   tags={"十三水房间"},
     *   summary="十三水房间管理列表",
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
                    'id': 157,
                    'cat_id': 4,
                    'room_name': '十三水--新手场',
                    'bottom_score': 500,
                    'sort_id': 0,
                    'status': 1,
                    'is_recommand': 0,
                    'type_id': 1,
                    'thirteen_poker_room': {
                        'type_name': '基础十三水'
                    }
                }
            ]
        }
    }",
     *   operationId="status",
     *   @SWG\Parameter(
     *     in="header",
     *     name="Accept",
     *     type="string",
     *     description="http头信息 *",
     *     required=true,
     *     default="Accept:application/vnd.agent.v1+json"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="type_id",
     *     type="number",
     *     description="游戏种类",
     *     required=true,
     *     default="1"
     *   ),
     *    @SWG\Parameter(
     *     in="formData",
     *     name="status",
     *     type="number",
     *     description="房间状态",
     *     required=true,
     *     default="1"
     *   ),
     *    @SWG\Parameter(
     *     in="formData",
     *     name="page",
     *     type="string",
     *     description="当前页",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="page_num",
     *     type="string",
     *     description="每页条数",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="is_page",
     *     type="number",
     *     description="是否分页,是否分页 1：是，0：否 ，默认1",
     *     required=true,
     *     default="1"
     *   ),
     *  @SWG\Parameter(
     *     in="formData",
     *     name="token",
     *     type="string",
     *     description="token",
     *     required=true,
     *     default="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0tMjAxLmRldi9hcGkvYXV0aG9yaXphdGlvbiIsImlhdCI6MTUwMTAzNjc2MCwiZXhwIjoxNTAxMjUyNzYwLCJuYmYiOjE1MDEwMzY3NjAsImp0aSI6IkhGQ0V3T1J0dFB3aW1CVFciLCJzdWIiOjF9.f2SgIGut3PJy3bPhkbsLklyd_wCr0pyjiL70XBnRMBs"
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function index(Request $request)
    {
        $type_id = $request->input('type_id');
        $status = $request->input('status');
        $page_num = $request->input('page_num',10);
        $is_page = $request->input('is_page', 1);

         // 关联数据游戏种类 统计信息
         $db =  RoomInfo::leftJoin('room_game_type', function ($join){
            $join->on("room_game_type.id", "=", "room_info.type_id")
                ->select('room_game_type.type_name');
        })->leftJoin('room_statistics', function ($join){
            $join->on("room_statistics.room_id", "=", "room_info.id")
                ->select('room_statistics.total_lose_money, room_statistics.total_winning_money');
        });

        $field = [
            'room_info.id',
            'room_info.room_name',
            'room_info.bottom_score',
            'room_info.status',
            'room_info.max_limit',
            'room_game_type.type_name',
            'room_statistics.total_lose_money',
            'room_statistics.total_winning_money',
        ];

        if(isset($type_id) && $type_id !=='') {
            $db->where('type_id',$type_id);
        }

        if(isset($status) && $status !== '') {

            $db->where('status',$status);
        }
        $db->where('status', '<>', 2);


        if(!$is_page) {
            $rooms = $db->get($field);
        } else {
            $rooms = $db->get($field)->paginate($page_num);
        }

        return $this->response->array([
            'code' => 0,
            'text' =>trans('room.success'),
            'result' => !$is_page? ['data' => $rooms]:$rooms,
        ]);
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/room/cat",
     *   tags={"十三水房间"},
     *   summary="十三水游戏分类",
     *   description="
     *   获取十三水游戏分类列表数据
     *   成功返回字段说明

    {
        'code': 0,
        'text': '操作成功',
        'result': {
            'data': [
                {
                    'id': 1,
                    'cat_id': 4,
                    'type_name': '基础十三水',
                    'sort_id': 100
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
    public function cat()
    {
        $cates = RoomGameType::orderby('sort_id','asc')->get();

        return $this->response->array([
            'code' => 0,
            'text' => trans('room.success'),
            'result' => [
                'data' => $cates,
            ],
        ]);
    }


    /**
     * consumes={"multipart/form-data"},
     * @SWG\Put(
     *   path="/room/status",
     *   tags={"十三水房间"},
     *   summary="修改十三水房间状态",
     *   description="
     *   成功返回字段说明
    {
        'code': 0,
        'text': '保存成功',
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
     *   @SWG\Parameter(
     *     in="formData",
     *     name="status",
     *     type="number",
     *     description="游戏是否可用,1为可用,0为不可用",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="room_id",
     *     type="number",
     *     description="房间ID",
     *     required=true,
     *     default="1"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="locale",
     *     type="string",
     *     description="语言",
     *     required=true,
     *     default="cn"
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
    public function updateStatus(Request $request)
    {

        if(!$rommId = $request->input('room_id')){
            return $this->response->array([
                'code'=>400,
                'text'=> trans('room.param_error'),
                'result'=>'',
            ]);
        }
        $room = RoomInfo::find($rommId);
        if(!$room) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('room.room_not_exist'),
                'result' => '',
            ]);
        }

        $validator = \Validator::make($request->input(), [
            'status' => 'required|integer|max:255',
        ]);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors(),
                'result'=>'',
            ]);
        }

        $attributes = $request->except('token','locale','room_id');

        $re = $room->where('id', $rommId)->update($attributes);


        $statusName = $attributes['status'] ==1 ? '启动':'禁用';
        if($re !== false) {
            @addLog(['action_name'=>'编辑十三水房间状态','action_desc'=>'房间名： '.$room['room_name'].'状态改为'.$statusName, 'action_passivity'=>'十三水房间列表']);

            return $this->response->array([
                'code' => 0,
                'text' =>trans('room.save_success'),
                'result' => '',
            ]);

        } else {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('room.save_fails'),
                'result' => '',
            ]);

        }

    }


    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/room/rules/show/{room_id}",
     *   tags={"十三水房间"},
     *   summary="电子游戏房间赔率方案显示",
     *   description="
     *   成功返回字段说明
    {
        'code': 0,
        'text': '操作成功',
        'result': {
            'data': [
                {
                    'id': 1,
                    'cat_id': 4,
                    'room_id': 1,
                    'room_name': '十三水—新手场',
                    'card_type': 1,
                    'play_name_type': '1',
                    'play_rules_odds': 68
                },
                {
                    'id': 2,
                    'cat_id': 4,
                    'room_id': 1,
                    'room_name': '十三水—新手场',
                    'card_type': 1,
                    'play_name_type': '1',
                    'play_rules_odds': 99
                }
            ]
        }
    }
    ",
     *   operationId="status",
     *   @SWG\Parameter(
     *     in="header",
     *     name="Accept",
     *     type="string",
     *     description="http头信息 *",
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
    public function showRules(Request $request,$id)
    {
        $roomRules = RoomRules::where('room_id',$id)
            ->get(['id as rule_id','room_id','room_name','card_type','play_name_type','play_rules_odds'])
            ->toArray();

        if(!$roomRules) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('room.room_not_exist'),
                'result' => '',
            ]);
        }

        return $this->response->array([
            'code' => 0,
            'text' => trans('room.success'),
            'result' => [
                'data' => $roomRules,
            ],
        ]);
    }

}