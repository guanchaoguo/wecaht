<?php
/**
 * 游戏厅限额
 * User: chensongjian
 * Date: 2017/4/6
 * Time: 14:11
 */

namespace App\Http\Controllers\V1;
use Illuminate\Http\Request;
use App\Models\HallLimitGroup;
use App\Models\HallLimitItem;
use App\Models\GameCat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HallQuotaController extends BaseController
{

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="hall/quota",
     *   tags={"游戏管理"},
     *   summary="游戏限额列表",
     *   description="
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': {
        'id': 2915,//限额组id
        'title': 'defaultC',//限额标题
        'hall_type': 3,//厅类型
        'limit_items': [//限制项目数组
            {
                'cat_name': '视频百家乐 ',//游戏分类名
                'game_cat_code': 'GC0001',//游戏分类标识符
                'game_cat_id': 1,//游戏分类id
                'bet_areas': [//下注区域数组
                    {
                        'betarea_code': 'G1001',//下注区域标识码
                        'bet_area': 1,//下注区域值
                        'max_money': '',//最小值
                        'min_money': ''//最大值
                    }
                ]
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
     *     default=""
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
     *     description="设定限额 defaultA,defaultB,defaultC",
     *     required=true,
     *     default="defaultA"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="hall_type",
     *     type="integer",
     *     description="厅类型 ，0：旗舰厅，1：贵宾厅，2：金臂厅，3：至尊厅",
     *     required=true,
     *     default="zh-cn"
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
        $message = [
            'title.*' => trans('agent.default_quota'),
            'hall_type.*' => trans('agent.game_hall_required'),
        ];
        $validator = \Validator::make($request->input(), [
            'title' => 'required|in:defaultA,defaultB,defaultC',
            'hall_type' => 'required|integer|exists:game_hall,id',
        ],$message);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        $title = $request->input('title', 'defaultA');
        $hall_type = $request->input('hall_type', 0);

        return $this->response->array([
            'code' => 0,
            'text' =>trans('agent.success'),
            'result' => self::getHallLimit($this->agentId, $hall_type, $title),
        ]);
    }

    /**
     * 获取游戏限额数据
     * @param int $agent_id 厅主id
     * @param int $hall_type 厅类型
     * @param string $title 限额组
     * @return array
     */
    private static function getHallLimit(int $agent_id = 0, int $hall_type = 0, string $title="defaultA") {
        //获取默认限额和厅主的限额
        $data = DB::table("hall_limit_group as g")->join("hall_limit_item as i", "g.id", "=" ,"i.group_id")
            ->select('g.id','g.agent_id','i.game_cat_id','i.max_money','i.min_money','i.bet_area')
            ->where('g.title', $title)
            ->where('g.hall_type', $hall_type)
            ->whereIn('g.agent_id', [$agent_id, 0])
            ->orderby('g.agent_id', 'asc')
            ->get();


        //合并去重（厅主有的限额覆盖平台的默认限额）确保限额完整
        $list = [];
        $id = 0;
        foreach ($data as $k => &$v)
        {
            if($agent_id != 0 && $v->agent_id != 0) {
                $id = $v->id;
            } elseif ($agent_id == 0 && $v->agent_id == 0) {
                $id = $v->id;
            }
            $tmp_key = $title . '-' . $hall_type . '-' . $v->game_cat_id . '-' . $v->bet_area;//以这个key作为键名，去重
            $v->betarea_code = config('betarea.'.$v->game_cat_id.'.'.$v->bet_area)['betarea_code'];//获取下去区域标识码
            unset($v->id, $v->agent_id);
            $list[$tmp_key] = (array)$v;
        }
        unset($v);
        //以游戏分类id分组，由于游戏限额是按游戏分类进行设置的
        $items = [];
        foreach ($list as $item) {
            $items[$item['game_cat_id']]['bet_areas'][] = $item;
        }

        //获取游戏分类默认限额数据格式
        $cat_data = self::getGameCat();

        foreach ($cat_data as $key => &$cat) {
            foreach ($cat['bet_areas'] as &$vv) {
                if (isset($items[$cat['game_cat_id']])) {
                    foreach ($items[$cat['game_cat_id']]['bet_areas'] as &$v3) {
                        if ($vv['bet_area'] == $v3['bet_area']) {
                            $vv = $v3;
                            break;
                        }
                    }
                    unset($v3);
                }else {
                    break;
                }
            }
            unset($vv);
        }
        unset($cat);

        $data = [
            'title' => $title,
            'hall_type' => $hall_type,
            'agent_id' => $agent_id,
            'limit_items' => $cat_data
        ];

        $id && $data['id'] = $id;
        return $data;

    }
    /**
     * 获取游戏分类默认限额数据格式
     * @return array
     */
    private static function getGameCat() : array
    {
        $data = [];
        //游戏分类
        $cat_data = GameCat::where('game_type',0)->select('cat_name','game_cat_code', 'id as game_cat_id')->get()->toArray();
        if($cat_data) {

            foreach ($cat_data as $k =>&$cat) {
                $bet_area = config('betarea.'.$cat['game_cat_id']);
                if(isset($bet_area)) {
                    $cat['bet_areas'] = array_values($bet_area);
                    foreach ($cat['bet_areas'] as &$v) {
                        $v['max_money'] = '';
                        $v['min_money'] = '';
                    }
                    unset($v);
                } else {
                    unset($cat_data[$k]);
                }

//                $data[$cat['game_cat_id']] = $cat;
            }
            unset($cat);
            $data = $cat_data;
        }
        return $data;
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Post(
     *   path="hall/quota",
     *   tags={"游戏管理"},
     *   summary="添加游戏限额",
     *   description="
     *   成功返回字段说明
        {
        'code': 0,
        'text': '保存成功',
        'result': ''
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
     *     default=""
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
     *     description="设定限额 defaultA,defaultB,defaultC",
     *     required=true,
     *     default="defaultA"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="hall_type",
     *     type="integer",
     *     description="厅类型 ，0：旗舰厅，1：贵宾厅，2：金臂厅，3：至尊厅",
     *     required=true,
     *     default="zh-cn"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="items",
     *     type="string",
     *     description="下注区域值 格式：[{'game_cat_id':1,'bet_areas': [{ 'bet_area': 1,'max_money': 1000,'min_money': 10}]}]",
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

        $message = [
            'title.*' => trans('agent.default_quota'),
            'hall_type.*' => trans('agent.game_hall_required'),
            'items.*' => trans('agent.items_area_required'),
        ];
        $validator = \Validator::make($request->input(), [
            'title' => 'required|in:defaultA,defaultB,defaultC',
            'hall_type' => 'required|integer|exists:game_hall,id',
            'items' => 'required',
        ], $message);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        $items = json_decode($request->input('items'), true);
        if( !$items ) {
            return $this->response->array([
                'code'=>400,
                'text'=>trans('agent.param_error'),
                'result'=>'',
            ]);
        }
        $title = $request->input('title');
        $hall_type = $request->input('hall_type');

        $attributes = [
            'title' => $title,
            'hall_type' => $hall_type,
            'item_type' => 1,//正常值
            'agent_id' => $this->agentId,
        ];
        //默认分组是否存在
        $info = HallLimitGroup::where($attributes)->first();

        if($info != null) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.limit_group_exist'),
                'result' => '',
            ]);

        }

        $attributes['status'] = 1;
        $attributes['uptime'] = date('Y-m-d H:i:s', time());

        DB::beginTransaction();

        $re = HallLimitGroup::create($attributes);
        $hall = ['旗舰厅', '贵宾厅','金臂厅' ,'至尊厅'];
        if($re){

            if($items) {
                $item_arr = [];

                foreach ($items as $item) {

                    foreach ($item['bet_areas'] as $v) {

                        if (!is_numeric($v['min_money']) || !is_numeric($v['max_money'])) {

                            DB::rollBack();
                            return $this->response->array([
                                'code' => 400,
                                'text' =>trans('agent.balance_str_error'),
                                'result' => '',
                            ]);

                        }
                        if ($v['min_money'] > $v['max_money']) {
                            DB::rollBack();
                            return $this->response->array([
                                'code' => 400,
                                'text' =>trans('agent.min_max_error'),
                                'result' => '',
                            ]);
                        }

                        $item_arr[] = [
                            'group_id' => $re['id'],
                            'game_cat_id' => $item['game_cat_id'],
                            'max_money' => $v['max_money'],
                            'min_money' => $v['min_money'],
                            'bet_area' => $v['bet_area'],
                        ];
                    }

                }

                $r = HallLimitItem::insert($item_arr);
                if($r) {
                    DB::commit();

                    // 添加操作日志
                    $hallName =  $hall[$hall_type];
                    @addLog([
                        'action_name'=> '游戏厅限额添加',
                        'action_desc'=> "游戏厅限额添加 添加厅为{$hallName}",
                        'action_passivity'=>'厅限额分组'
                    ]);

                    //将修改后更新缓存
                    $this->setCacaheQuota();

                    return $this->response->array([
                        'code' => 0,
                        'text' =>trans('agent.save_success'),
                        'result' => '',
                    ]);

                } else {

                    DB::rollBack();

                    return $this->response->array([
                        'code' => 400,
                        'text' =>trans('agent.add_fails'),
                        'result' => '',
                    ]);

                }
            }

            // 添加操作日志
            $hallName =  $hall[$hall_type];
            @addLog([
                 'action_name'=> '游戏厅限额添加',
                 'action_desc'=> "游戏厅限额添加 添加厅为{$hallName}",
                'action_passivity'=>'厅限额分组'
            ]);

            return $this->response->array([
                'code' => 0,
                'text' =>trans('agent.save_success'),
                'result' => '',
            ]);
            DB::commit();


        } else {

            DB::rollBack();

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.add_fails'),
                'result' => '',
            ]);
        }
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Put(
     *   path="hall/quota/{id}",
     *   tags={"游戏管理"},
     *   summary="编辑保存游戏限额",
     *   description="
     *   {id} = 限额分组id
     *   成功返回字段说明
        {
        'code': 0,
        'text': '保存成功',
        'result': ''
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
     *     default=""
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
     *     description="设定限额 defaultA,defaultB,defaultC",
     *     required=true,
     *     default="defaultA"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="hall_type",
     *     type="integer",
     *     description="厅类型 ，0：旗舰厅，1：贵宾厅，2：金臂厅，3：至尊厅",
     *     required=true,
     *     default="zh-cn"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="items",
     *     type="string",
     *     description="下注区域值 格式：[{'game_cat_id':1,'bet_areas': [{ 'bet_area': 1,'max_money': 1000,'min_money': 10}]}]",
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
        $where = [
            'id' => $id,
            'agent_id' => $this->agentId,
            'item_type' => 1,//正常值
        ];
        $info = HallLimitGroup::where($where)->first();
        if($info == null) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.limit_group_not_exist'),
                'result' => '',
            ]);
        }
        $items = json_decode($request->input('items'), true);

        if($items) {
            $item_arr = [];
            foreach ($items as $item) {

                foreach ($item['bet_areas'] as $v) {

                    if (!is_numeric($v['min_money']) || !is_numeric($v['max_money'])) {

                        return $this->response->array([
                            'code' => 400,
                            'text' =>trans('agent.balance_str_error'),
                            'result' => '',
                        ]);

                    }
                    if ($v['min_money'] > $v['max_money']) {
                        return $this->response->array([
                            'code' => 400,
                            'text' =>trans('agent.min_max_error'),
                            'result' => '',
                        ]);
                    }

                    $item_arr[] = [
                        'group_id' => $id,
                        'game_cat_id' => $item['game_cat_id'],
                        'max_money' => $v['max_money'],
                        'min_money' => $v['min_money'],
                        'bet_area' => $v['bet_area'],
                    ];
                }

            }
            DB::beginTransaction();
            //delete old data
            HallLimitItem::where('group_id', $id)->delete();

            // add datas
            $r = HallLimitItem::insert($item_arr);
            if($r) {
                DB::commit();

                // 添加操作日志
                @addLog([
                    'action_name'=> '游戏厅限额修改',
                    'action_desc'=> "游戏厅限额修改 限额分组ID{$id}",
                    'action_passivity'=>'厅限额分组'
                ]);

                //将修改后更新缓存
                $this->setCacaheQuota();

                return $this->response->array([
                    'code' => 0,
                    'text' =>trans('agent.save_success'),
                    'result' => '',
                ]);

            } else {
                DB::rollBack();
                return $this->response->array([
                    'code' => 400,
                    'text' =>trans('agent.save_fails'),
                    'result' => '',
                ]);

            }

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
     * @SWG\Post(
     *   path="hall/quota/shortcut",
     *   tags={"游戏管理"},
     *   summary="快捷设定限额（添加）",
     *   description="
     *   成功返回字段说明
    {
    'code': 0,
    'text': '保存成功',
    'result': ''
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
     *     default=""
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
     *     description="设定限额 defaultA,defaultB,defaultC",
     *     required=true,
     *     default="defaultA"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="hall_type",
     *     type="integer",
     *     description="厅类型 ，0：旗舰厅，1：贵宾厅，2：金臂厅，3：至尊厅",
     *     required=true,
     *     default="zh-cn"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="game_cat_id",
     *     type="string",
     *     description="游戏分类id [1,2,3,4]",
     *     required=true,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="max_money",
     *     type="number",
     *     description="最大值",
     *     required=true,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="min_money",
     *     type="number",
     *     description="最小值",
     *     required=true,
     *     default=""
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function shortcutStore(Request $request)
    {
        if($this->agentInfo['grade_id'] == 2) {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.grade_id_error'),
                'result' => '',
            ]);
        }
        $message = [
            'title.*' => trans('agent.default_quota'),
            'hall_type.*' => trans('agent.game_hall_required'),
        ];
        $validator = \Validator::make($request->input(), [
            'title' => 'required|in:defaultA,defaultB,defaultC',
            'game_cat_id' => 'required',
            'hall_type' => 'required|integer|exists:game_hall,id',
            'max_money' => 'required|numeric',
            'min_money' => 'required|numeric',
        ], $message);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        $title = $request->input('title');
        $hall_type = $request->input('hall_type');
        $game_cat_id = json_decode($request->input('game_cat_id'),true);
        $max_money = $request->input('max_money');
        $min_money = $request->input('min_money');

        if ( $min_money === '' ) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('gamebalance.min_balance_is_null'),
                'result' => '',
            ]);
        }

        if ( $max_money === '' ) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('gamebalance.max_balance_is_null'),
                'result' => '',
            ]);
        }

        if (!is_numeric($min_money)) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('gamebalance.min_balance_str_error'),
                'result' => '',
            ]);
        }

        if (!is_numeric($max_money)) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('gamebalance.max_balance_str_error'),
                'result' => '',
            ]);
        }

        if($min_money <= 0) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('gamebalance.min_balance_is_not_0'),
                'result' => '',
            ]);
        }

        if($min_money > $max_money) {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.min_max_error'),
                'result'=>'',
            ]);
        }
        $attributes = [
            'title' => $title,
            'hall_type' => $hall_type,
            'item_type' => 1,
            'agent_id' => $this->agentId,
        ];
        //默认分组是否存在
        $info = HallLimitGroup::where($attributes)->first();
        if($info != null) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.limit_group_exist'),
                'result' => '',
            ]);

        }

        $attributes['status'] = 1;
        $attributes['uptime'] = date('Y-m-d H:i:s', time());

        DB::beginTransaction();
        $re = HallLimitGroup::create($attributes);
        if($re){
            if($game_cat_id && is_array($game_cat_id)) {
                $item_arr = [];

                foreach ($game_cat_id as $v) {
                    $betarea = config('betarea.'.$v);

                    if($betarea) {
                        foreach ($betarea as $vv){
                            $item_arr[] = [
                                'group_id' => $re['id'],
                                'game_cat_id' => $v,
                                'max_money' => $max_money,
                                'min_money' => $min_money,
                                'bet_area' => $vv['bet_area'],
                            ];
                        }
                    }
                }
                $r = HallLimitItem::insert($item_arr);
                if($r) {
                    DB::commit();


                    $hall = ['旗舰厅', '贵宾厅', '金臂厅','至尊厅'];
                    $hallName =  $hall[$hall_type];
                    @addLog([
                        'action_name'=> '快捷设定限额（添加）',
                        'action_desc'=> "快捷设定限额（添加） 限额添加厅为{$hallName}",
                        'action_passivity'=>'厅限额分组'
                    ]);

                    //将修改后更新缓存
                    $this->setCacaheQuota();

                    return $this->response->array([
                        'code' => 0,
                        'text' =>trans('agent.save_success'),
                        'result' => '',
                    ]);

                } else {

                    DB::rollBack();

                    return $this->response->array([
                        'code' => 400,
                        'text' =>trans('agent.add_fails'),
                        'result' => '',
                    ]);

                }
            }
            DB::commit();


        } else {

            DB::rollBack();

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.add_fails'),
                'result' => '',
            ]);
        }
    }


    /**
     * consumes={"multipart/form-data"},
     * @SWG\Put(
     *   path="hall/quota/shortcut/{id}",
     *   tags={"游戏管理"},
     *   summary="快捷设定限额（保存）",
     *   description="
     *   {id} = 限额分组id
     *   成功返回字段说明
    {
    'code': 0,
    'text': '保存成功',
    'result': ''
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
     *     default=""
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
     *     description="设定限额 defaultA,defaultB,defaultC",
     *     required=true,
     *     default="defaultA"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="hall_type",
     *     type="integer",
     *     description="厅类型 ，0：旗舰厅，1：贵宾厅，2：金臂厅，3：至尊厅",
     *     required=true,
     *     default="zh-cn"
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="game_cat_id",
     *     type="string",
     *     description="游戏分类id [1,2,3,4]",
     *     required=true,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="max_money",
     *     type="number",
     *     description="最大值",
     *     required=true,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="query",
     *     name="min_money",
     *     type="number",
     *     description="最小值",
     *     required=true,
     *     default=""
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function shortcutUpdate(Request $request, int $id)
    {
        if($this->agentInfo['grade_id'] == 2) {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.grade_id_error'),
                'result' => '',
            ]);

        }
        $where = [
            'id' => $id,
            'agent_id' => $this->agentId,
        ];
        $info = HallLimitGroup::where($where)->first();
        if($info == null) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.limit_group_not_exist'),
                'result' => '',
            ]);
        }

        $game_cat_id = json_decode($request->input('game_cat_id'),true);
        $max_money = $request->input('max_money');
        $min_money = $request->input('min_money');

        if ( $min_money === '' ) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('gamebalance.min_balance_is_null'),
                'result' => '',
            ]);
        }

        if ( $max_money === '' ) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('gamebalance.max_balance_is_null'),
                'result' => '',
            ]);
        }

        if (!is_numeric($min_money)) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('gamebalance.min_balance_str_error'),
                'result' => '',
            ]);
        }

        if (!is_numeric($max_money)) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('gamebalance.max_balance_str_error'),
                'result' => '',
            ]);
        }

        if($min_money <= 0) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('gamebalance.min_balance_is_not_0'),
                'result' => '',
            ]);
        }

        if($min_money > $max_money) {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.min_max_error'),
                'result'=>'',
            ]);
        }

        if($game_cat_id && is_array($game_cat_id)) {

            $item_arr = [];
            foreach ($game_cat_id as $v) {
                $betarea = config('betarea.'.$v);

                if($betarea) {
                    foreach ($betarea as $vv){
                        $item_arr[] = [
                            'group_id' => $id,
                            'game_cat_id' => $v,
                            'max_money' => $max_money,
                            'min_money' => $min_money,
                            'bet_area' => $vv['bet_area'],
                        ];
                    }
                }
            }
            DB::beginTransaction();
            //delete old data
            HallLimitItem::where('group_id', $id)->whereIn('game_cat_id', $game_cat_id)->delete();
            // add data
            $r = HallLimitItem::insert($item_arr);
            if($r) {
                DB::commit();

               // 添加操作日志
               @addLog([
                    'action_name'=> '快捷设定限额（修改）',
                    'action_desc'=> "快捷设定限额（修改） 限额添加ID{$id}",
                    'action_passivity'=>'厅限额分组'
                ]);

                //将修改后更新缓存
                $this->setCacaheQuota();

                return $this->response->array([
                    'code' => 0,
                    'text' =>trans('agent.save_success'),
                    'result' => '',
                ]);

            } else {
                DB::rollBack();
                return $this->response->array([
                    'code' => 400,
                    'text' =>trans('agent.save_fails'),
                    'result' => '',
                ]);

            }

        } else {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.save_fails'),
                'result' => '',
            ]);
        }


    }

    /**
     * 修改厅限额缓存
     * @param array $item
     */
    private  function setCacaheQuota()
    {
        $keyName = 'hall_limit:' . $this->agentId;

        //获取默认限额和厅主的限额
        $data = DB::table("hall_limit_group as g")->join("hall_limit_item as i", "g.id", "=" ,"i.group_id")
            ->select('g.title','g.hall_type','i.group_id', 'i.game_cat_id','i.max_money','i.min_money','i.bet_area')
            ->whereIn('g.agent_id', [$this->agentId, 0])
            ->orderby('g.agent_id','asc')
            ->get();

        //合并去重（厅主有的限额覆盖平台的默认限额）确保限额完整
        $list = [];
        foreach ($data as $k => &$v)
        {
            $tmp_key = $v->title . '-' . $v->hall_type . '-' . $v->game_cat_id . '-' . $v->bet_area;//以这个key作为键名，去重
            unset($v->title);
            $list[$tmp_key] = (array)$v;
        }

        if(count($list)) {
            $redis = Redis::connection("default");
            $redis->del($keyName);
            foreach (StringShiftToInt($list, ['hall_type','group_id','game_cat_id','max_money','min_money','bet_area']) as $item){
                $redis->rpush($keyName,  json_encode($item));
            }
        }

    }
}