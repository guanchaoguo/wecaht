<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkl.com
 * Date: 2017/2/14
 * Time: 10:41
 * 后台角色相关操作控制器
 */

namespace App\Http\Controllers\V1;

use App\Http\Controllers\V1\BaseController;
use App\Models\Agent;
use App\Models\Menu;
use App\Models\PlatformUser;
use App\Models\RoleGroupMenu;
use App\Models\UserMenu;
use Illuminate\Http\Request;
use App\Models\RoleGroup;
use Illuminate\Support\Facades\DB;

class RoleController extends BaseController
{


    /**
     * consumes={"multipart/form-data"},
     * @SWG\Post(
     *   path="/role",
     *   tags={"系统管理"},
     *   summary="厅主获取角色分组列表",
     *   description="
     *   厅主获取角色分组列表
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': {
    'total': 1, //总的页码
    'per_page': 10, //每页数据条数
    'current_page': 1,	//当前分页
    'last_page': 1,	//最后一页
    'next_page_url': null,
    'prev_page_url': null,
    'from': 1,
    'to': 1,
    'data': [
    {
    'id': 1,	//角色分组ID
    'group_name': '游戏运营组',//角色分组名称
    'desc': '游戏运营组',//分组描述
    'state': 1,//是否启用，0为不启用，1为启用，2为已删除，默认是1
    'add_time': '2017-03-31 13:54:23'//添加时间
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
     *     default="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vYWdlbnQtYXBpLmRldi9hcGkvYXV0aG9yaXphdGlvbiIsImlhdCI6MTQ5MDc3MTA0NywiZXhwIjoxNDkwOTg3MDQ3LCJuYmYiOjE0OTA3NzEwNDcsImp0aSI6InBYblFwbnV3c1N6b3JhMEEiLCJzdWIiOjJ9.8OUMTZTK7sovzwduyq7c94UJjcTxOjWT9SFluk7fMko"
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function index(Request $request)
    {
        $page_num = $request->input('page_num',10);
        $roleList = RoleGroup::where('state','=',1)->where(['agent_id'=>$this->agentId])->orderBy('id','desc')->paginate($page_num);

        //数据为空时
        if(!$roleList)
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => trans('role.empty_list'),
                'result' => ''
            ]);
        }

        //数据请求成功返回
        return $this->response()->array([
            'code'      => 0,
            'text'      => trans('role.success'),
            'result'    => $roleList
        ]);
    }
    /**
     * consumes={"multipart/form-data"},
     * @SWG\Post(
     *   path="/role/store",
     *   tags={"系统管理"},
     *   summary="厅主添加角色分组",
     *   description="
     *   厅主添加角色分组
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': {
    'id': 2 //新添加成功的分组ID
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
     *     name="group_name",
     *     type="string",
     *     description="分组名称",
     *     required=true,
     *     default="abc"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="desc",
     *     type="string",
     *     description="分组描述",
     *     required=true,
     *     default="描述"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="id",
     *     type="number",
     *     description="分组ID,如果为添加则不用传，修改是传入",
     *     required=true,
     *     default="描述"
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
    public function store(Request $request)
    {
        $message = [
            'group_name.required'    => trans('role.group_name.required'),
            'group_name.max'        => trans('role.group_name.max')
        ];

        //先进行数据验证操作
        $validator = \Validator::make($request->input(),[
            'group_name'    => 'required|max:45'
        ],$message);

        //验证失败返回验证错误信息
        if ($validator->fails())
        {
            return $this->response()->array([
                'code'      => 400,
                'text'      => $validator->errors(),
                'result'    => ''
            ]);
        }

        //验证通过进行数据的添加操作
        $attributes = $request->except('token','locale','s');//过滤掉token 和 locale字段
        $attributes['agent_id'] = $this->agentId;
        $id = $request->input('id',0);
        if($id > 0)
        {//编辑操作
            $attributes['add_time'] = date("Y-m-d H:i:s",time());
            $res = RoleGroup::where(['id'=>$id])->update($attributes);
            if($res)
                $res = $id;
        }
        else
        {
            $attributes['add_time'] = date("Y-m-d H:i:s",time());
            $res = RoleGroup::insertGetId($attributes);
        }
//        $res = RoleGroup::insertGetId($attributes);

        //数据写入失败返回错误信息
        if(!$res)
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => trans('role.fails'),
                'result'    => ''
            ]);
        }


        // 添加操作日志
        @addLog([
            'action_name'=>'厅主添加角色分组',
            'action_desc'=> "厅主添加角色分组; 分组名称{$request->input( 'group_name')}",
            'action_passivity'=>'用户权限组'
        ]);

        //数据写入成功返回成功信息
        return $this->response()->array([
            'code'      => 0,
            'text'      => trans('role.success'),
            'result'    => [
                'id'    => $res
            ]
        ]);


    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Post(
     *   path="/role/menus/{id}",
     *   tags={"系统管理"},
     *   summary="厅主编辑角色权限时获取菜单数据",
     *   description="
     *   厅主编辑角色权限时获取菜单数据
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': {
    'allRole': [
    {
    'id': 1,
    'parent_id': 0,
    'title_cn': '账号管理',
    'title_en': '',
    'class': 0,
    'desc': null,
    'link_url': '/accountManage',
    'icon': '',
    'state': 1,
    'sort_id': 1,
    'menu_code': 'M1001',
    'isHaveRole': 0,
    '_child': [
    {
    'id': 2,	//菜单ID
    'parent_id': 1,	//菜单父级ID
    'title_cn': '代理管理',	//菜单中文名称
    'title_en': '',	//菜单英文名称
    'class': 0,	//菜单类别
    'desc': null,	//菜单描述
    'link_url': '/accountManage/AgentM',	//菜单路由URL
    'icon': '',	//菜单图标
    'state': 1,	//状态：1启用，0禁用
    'sort_id': 1, //排序
    'menu_code': 'M1003',//菜单编码
    'isHaveRole': 0		//当前角色是否拥有该菜单权限，1拥有，0为否
    },
    ]
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
     *     default="en"
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
    public function showMenus(Request $request,$id)
    {
        //首先验证编辑的数据是否存在
        $groupFind = RoleGroup::find($id);
        if(!$groupFind)
        {
            //数据不存在返回错误提示
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('role.fails'),
                'result'    => ''
            ]);
        }

        //获取所有菜单信息
        $agentInfo = $this->agentInfo;
        $menusList = Menu::orderBy('sort_id','desc')->where('state',1)->where(['grade_id'=>$agentInfo['grade_id']])->get()->toArray();
        //获取角色组已有菜单权限
        $userHaveRole = array_column(RoleGroupMenu::where('role_id',$id)->select('menu_id')->get()->toArray(),'menu_id');
        //遍历查看角色组是否已经拥有该权限
        foreach ($menusList as $k=>$v)
        {
            if(in_array($v['menu_id'],$userHaveRole))
            {
                $menusList[$k]['isHaveRole'] = 1;
            }
            else
            {
                $menusList[$k]['isHaveRole'] = 0;
            }
        }
        $parentids = array_unique(array_column($menusList,'parent_id'));
        $usermenusIds = array_column($menusList,'menu_id');
        $sysMenus = DB::table('agent_system_menus')->orderBy('sort_id')->where('state',1)->get()->toArray();
        $parentMenus = [];
        foreach ($sysMenus as $k=>$v)
        {
            if(in_array($v->id,$parentids) && !in_array($v->id,$usermenusIds))
            {
                $parentMenus[] = [
                    'id'    => $v->id,
                    'menu_id'   => $v->id,
                    'parent_id' => $v->parent_id,
                    'title_cn'  => $v->title_cn,
                    'title_en'  => $v->title_en,
                    'class' => $v->class,
                    'desc'  => $v->desc,
                    'link_url'   =>$v->link_url,
                    'state' => $v->state,
                    'sort_id'   => $v->sort_id,
                    'menu_code' => $v->menu_code,
                ];
            }
        }
        $menusList = array_merge($parentMenus,$menusList);
        $menusTree = get_attr($menusList,'0');

        //没有获取到菜单数据返回错误信息
        if(!$menusTree)
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => trans('role.empty_list'),
                'result'    => ''
            ]);
        }

        //返回菜单列表数据
        return $this->response()->array([
            'code'  => 0,
            'text'  => trans('role.success'),
            'result'    => [
                'allRole'   => $menusTree,
            ]
        ]);
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Patch(
     *   path="/role/{id}",
     *   tags={"系统管理"},
     *   summary="厅主编辑保存角色权限",
     *   description="
     *   厅主编辑保存角色权限
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
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="roles",
     *     type="string",
     *     description="菜单数据",
     *     required=true,
     *     default="en"
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
    public function updateRole(Request $request,$id)
    {
        //获取需要编辑的数据信息
        $roleGroup  = RoleGroup::find($id);
        if(!$roleGroup)//需要编辑的数据错误
        {
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('role.data_error'),
                'result'    => ''
            ]);
        }

        //进行角色分组权限选择验证操作
        $messages = [
            'roles.required'    => trans('role.roles.required')
        ];

        $validator = \Validator::make($request->input(),[
            'roles'     => 'required'
        ],$messages);

        //数据格式验证错误，返回错误信息
        if($validator->fails())
        {
            return $this->response()->array([
                'code'      => 400,
                'text'      => $validator->errors(),
                'result'    => ''
            ]);
        }

       /**
        * 数据通过格式验证，开始进行角色权限字段拆解操作
        * 前后端约定格式为 roles =>[menu_id-parent_id,menu_id-parent_id....]
        * 而数据库中需要获取到每个权限菜单的父级ID
        * 所以需要进行数据拆解或者到父级ID
        */
        $rolesList = [];
       if(is_array($request->input('roles')))
       {
           foreach ($request->input('roles') as $v)
           {
               $menus = explode('-',$v);
                $rolesList[] = [
                    'role_id'   => $id,
                    'menu_id'   => $menus[0],
                    'parent_id' => $menus[1]
                ];
           }
       }

       //如果数据拆解错误，证明前端提交数据格式错误，返回错误信息
        if(empty($rolesList))
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => trans('role.data_error'),
                'result'    => ''
            ]);
        }

        //判断是否需要进行删除旧数据操作
        $roleMenus  = RoleGroupMenu::where('role_id',$id)->get()->toArray();
        if(!$roleMenus)
        {//不存在则进行添加操作
            $res = RoleGroupMenu::insert($rolesList);
            if(!$res)
            {
                return $this->response()->array([
                    'code'  => 400,
                    'text'  => trans('role.fails'),
                    'result'   => ''
                ]);
            }

            return $this->response()->array([
                'code'  => 0,
                'text'  => trans('role.success'),
                'result'    => ''
            ]);
        }
        else
        {//数据存在，则要进行删除旧数据，然后进行写入操作，需要用到事物进行控制
            DB::beginTransaction();
            try
            {
                $de = RoleGroupMenu::where('role_id',$id)->delete();
                if(!$de)
                {
                    throw new \Exception("delete error");
                }
                $ins = RoleGroupMenu::insert($rolesList);
                if(!$ins)
                {
                    throw new \Exception('insert error');
                }
                DB::commit();//事物提交


                 // 添加操作日志
                @addLog([
                    'action_name'=>'保存分组角色菜单权限信息',
                    'action_desc'=> "修改的分组角色名称为: ".$roleGroup->group_name,
                    'action_passivity'=>'用户权限组',
                ]);

                return $this->response()->array([
                    'code'  => 0,
                    'text'  => trans('role.success'),
                    'result'    => ''
                ]);
            }
            catch (\Exception $e)
            {
                DB::rollBack();//事物回滚

                return $this->response()->array([
                    'code'  => 400,
                    'text'  => trans('role.fails'),
                    'result'   => ''
                ]);
            }

        }
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Delete(
     *   path="/role/{id}",
     *   tags={"系统管理"},
     *   summary="厅主删除分组分组操作",
     *   description="
     *   厅主删除分组分组操作
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
    public function deleteGroup(Request $request,$id)
    {
        //获取需要删除的信息
        $findGroup = RoleGroup::where('state','=',1)->find($id);
        if(!$findGroup)
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => trans('role.data_error'),
                'result'   => ''
            ]);
        }

        //进行删除操作，删除动作前提是该角色分组下没有子账户
        $subAccount = Agent::where('group_id',$id)->whereIn('account_state',[1,2])->select('id')->get()->toArray();
        //如果底下存在子帐号，则提示不能进行删除操作，需要把其底下的子账户删除后才能进行删除操作
        if($subAccount)
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => trans('role.sub_account'),
                'result'    => ''
            ]);
        }

        //如果没有子账户信息，则进行软删除操作
        $res = RoleGroup::where('id',$id)->update(['state'=>2]);
        //操作失败
        if(!$res)
        {
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('role.fails'),
                'result'    => ''
            ]);
        }

 
         // 添加操作日志
         @addLog([
           'action_name'=>' 删除角色分组操作',
            'action_desc'=> " 删除角色分组操作;  子账户名称{$subAccount->user_name} ID{$id}",
            'action_passivity'=>'用户权限组',
        ]);


        //操作成功
        return $this->response()->array([
            'code'      => 0,
            'text'      => trans('role.success'),
            'result'    => ''
        ]);

    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Post(
     *   path="/role/account/list",
     *   tags={"系统管理"},
     *   summary="厅主查看子账号列表",
     *   description="
     *   厅主查看子账号列表
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
    'id': 82,	//
    'user_name': 'agent_test1',	//登录账号
    'desc': null,		//描述
    'account_state': 1,	//1为正常,2为停用,3为删除
    'add_time': '2017-04-01 10:38:20',	//添加时间
    'update_time': '2017-04-01 10:38:20',	//登录时间
    'ip_info': null,	//IP
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
     *     default="en"
     *   ),
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="page_num",
     *     type="string",
     *     description="每页显示数据条数",
     *     required=true,
     *     default="en"
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
    public function accountList(Request $request)
    {
        $page_num = $request->input('page_num',10);
        $platformList = Agent::select('id','user_name','desc','account_state','add_time','update_time','ip_info','email','group_id')->where('account_state','<',3)->where(['parent_id'=>$this->agentId])->where(['is_hall_sub'=>1])->orderBy('id','desc')->paginate($page_num);

        foreach ($platformList as $v) {
            if( ! $v->roleGroup ) {
                unset($v->roleGroup);
                $v->role_group = ['group_name' => ''];
            }
        }

        //数据为空时
        if(!$platformList)
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => trans('role.empty_list'),
                'result' => ''
            ]);
        }

        //数据请求成功返回
        return $this->response()->array([
            'code'      => 0,
            'text'      => trans('role.success'),
            'result'    => $platformList
        ]);
    }
    /**
     * consumes={"multipart/form-data"},
     * @SWG\Post(
     *   path="/role/account",
     *   tags={"系统管理"},
     *   summary="厅主添加子账号",
     *   description="
     *   厅主添加子账号
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result':  {
    'id': '115'
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
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="user_name",
     *     type="string",
     *     description="用户名",
     *     required=true,
     *     default="en"
     *   ),
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="email",
     *     type="string",
     *     description="邮箱",
     *     required=true,
     *     default="en"
     *   ),
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="password",
     *     type="string",
     *     description="密码",
     *     required=true,
     *     default="en"
     *   ),
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="password_confirmation",
     *     type="string",
     *     description="密码确认",
     *     required=true,
     *     default="en"
     *   ),
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="id",
     *     type="number",
     *     description="数据ID，如果是添加则不用传，修改则传ID",
     *     required=true,
     *     default="en"
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
    public function addAccount(Request $request)
    {
        $id = $request->input('id',0);
        //数据验证
        $message = [
            'user_name.required'    => trans('role.user_name.required'),
            'user_name.max'         => trans('role.user_name.max'),
            'user_name.min'         => trans('role.user_name.min'),
            'user_name.regex' => trans('role201.user_name.regex'),
            'password.required'     => trans('role.password.required'),
            'password.max'          => trans('role.password.max'),
            'password.min'          => trans('role.password.min'),
            'password.confirmation' => trans('role.password.confirmation'),
            'desc.max'              => trans('role.desc.max')
        ];

        $validator = \Validator::make($request->input(),[
            'user_name'    => [
                'required',
                'regex:/^[a-zA-z][a-zA-Z0-9_]{3,45}$/'
            ],
            'password'  => 'required|confirmed|max:20|min:6',
            'desc'      => 'max:45',
            'email'     => 'required|email'
        ],$message);

        if($validator->fails())
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => $validator->errors(),
                'result'    => ''
            ]);
        }

        //验证用户登陆名唯一性
        if($id == 0)
        {
            $find = Agent::where('user_name','=',$request->input('user_name'))->where('account_state','<',3)->first();
        }
        else
        {
            $find = false;
        }
       if($find)
        {
            return $this->response->array([
                'code'  => 400,
                'text'  => trans('role.user_exists'),
                'result'    => ''
            ]);
        }

        //验证邮箱唯一性
        if($id == 0)
        {
            $email = Agent::where(['email'=>$request->input('email')])->where('account_state','<',3)->first();
        }
        else
        {
            $email = false;
        }

        if($email)
        {
            return $this->response->array([
                'code'  => 400,
                'text'  => trans('role.email_exists'),
                'result'    => ''
            ]);
        }

        //数据验证通过进行数据添加操作
        $attributes = $request->except('token','locale','password_confirmation','s');//过滤掉token 和 locale字段
        $salt = randomkeys(20);
        $attributes['salt'] = $salt;
        $attributes['password'] = app('hash')->make($request->input('password').$salt);
        $attributes['parent_id'] = $this->agentId;
        $attributes['is_hall_sub'] = 1;
        $attributes['grade_id'] = 1;
        $attributes['ip_info'] =  $_SERVER["REMOTE_ADDR"];
        $attributes['add_time'] = date("Y-m-d H:i:s",time());
        $attributes['update_time'] = date("Y-m-d H:i:s",time());

        if($id > 0)
        {//编辑操作
            $res = Agent::where(['id'=>$id])->update($attributes);
            if($res)
                $res = $id;
        }
        else
        {
            $res = Agent::insertGetId($attributes);
        }

        //账号创建失败返回错误信息
        if(!$res)
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => trans('role.fails'),
                'result'    => ''
            ]);
        }


        // 添加操作日志
        @addLog([
            'action_name'=>'添加子账号操作',
            'action_desc'=> "添加子账号操作; 名称{$request->input('user_name')}",
            'action_passivity'=>'代理商账号表'
        ]);


        //创建成功返回成功信息和ID
        return $this->response()->array([
            'code'  => 0,
            'text'  => trans('role.success'),
            'result'    => [
                'id'    => $res
            ]
        ]);
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Patch(
     *   path="/role/account/state/{id}",
     *   tags={"系统管理"},
     *   summary="厅主修改子账号状态",
     *   description="
     *   厅主修改子账号状态
     *   PS:该接口为公用型接口，子账号的删除、停用、启用都是调用该接口
     *     只是具体的状态值不一样；state状态值：1为正常,2为停用,3为删除
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
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="state",
     *     type="string",
     *     description="状态",
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
    public function accountState(Request $request,$id)
    {
        $state = $request->input('state');
        $validator = \Validator::make($request->input(),[
            'state'     => 'required|in:1,2,3'
        ]);
        if($validator->fails())
        {
            return $this->response()->array([
                'code'      => 400,
                'text'      => $validator->errors(),
                'result'    => ''
            ]);
        }
        //获取需要修改状态的数据信息
        $findAccount = Agent::find($id);
        if(!$findAccount)
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  =>trans('role.data_error'),
                'result'    => ''
            ]);
        }

        //进行账号的状态修改操作
        if($state == 3)
        {
            $res = Agent::where('id',$id)->where(['is_hall_sub'=>1])->delete();
        }else
        {
            $res = Agent::where('id',$id)->where(['is_hall_sub'=>1])->update(['account_state'=>$state]);
        }


        if(!$res)
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => trans('role.fails'),
                'result'    => ''
            ]);
        }

        $stat = [ 1=>'启用',2=>'停用',3=>'删除'];
        $statName = $stat[$state];
        @addLog([
            'action_name'=>'修改子账号状态操作',
            'action_desc'=> $statName."账号; 名称{$findAccount->user_name} ID{$id}",
            'action_passivity'=>'代理商账号表'
        ]);

        return $this->response()->array([
            'code'  => 0,
            'text'  => trans('role.success'),
            'result'   =>''
        ]);
    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Patch(
     *   path="/role/account/editPwd/{id}",
     *   tags={"系统管理"},
     *   summary="厅主修改子账号密码",
     *   description="
     *   厅主修改子账号密码
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
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="password",
     *     type="string",
     *     description="密码",
     *     required=true,
     *     default="111111"
     *   ),
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="password_confirmation",
     *     type="string",
     *     description="密码确认",
     *     required=true,
     *     default="111111"
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
    public function accountEditPwd(Request $request,$id)
    {
        //获取需要编辑的子帐号信息
        $findSub = Agent::find($id);
        if(!$findSub)
        {//子帐号不存在返回错误信息
            return $this->response()->array([
                'code'  => 400,
                'text'  => trans('role.data_error'),
                'result'    => ''
            ]);
        }

        //进行提交数据验证
        $message = [
            'password.required'     => trans('role.password.required'),
            'password.max'          => trans('role.password.max'),
            'password.min'          => trans('role.password.min'),
            'password.confirmation' => trans('role.password.confirmation'),
        ];
        $validator = \Validator::make($request->input(),[
            'password'  => 'required|confirmed|max:20|min:6',
        ],$message);

        //数据验证不通过
        if($validator->fails())
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => $validator->errors(),
                'result'    => ''
            ]);
        }

        //数据验证通过进行密码修改操作
        $attributes = $request->except('token','locale','password_confirmation','s');//过滤掉token 和 locale字段
        $attributes['password'] = app('hash')->make($request->input('password').$findSub->salt);
        $res = Agent::where('id',$id)->where(['is_hall_sub'=>1])->update($attributes);

        //密码修改操作失败
        if(!$res)
        {
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('role.fails'),
                'result'    => ''
            ]);
        }

       // 添加操作日志
        @addLog([
            'action_name'=>'修改子账号密码操作',
            'action_desc'=> "修改子账号密码操作; 名称{$findSub->user_name} ID{$id}",
            'action_passivity'=>'代理商账号表'
        ]);


        //密码修改成功返回
        return $this->response()->array([
            'code'      => 0,
            'text'      => trans('role.success'),
            'result'    => ''
        ]);

    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Post(
     *   path="/role/account/menus/{id}",
     *   tags={"系统管理"},
     *   summary="厅主修改子账号权限获取数据",
     *   description="
     *   厅主修改子账号权限获取数据
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': [
    {
    'id': 2,
    'group_name': '用户运营组', //角色组名称
    'isHaveRole': 0,	//是否拥有该权限
    'roles': [
    {
    'id': 1,	//菜单ID
    'parent_id': 0,	//父级ID
    'menu_code': 'M1001',	//菜单编码
    'isHaveRole': 0,	//是否拥有该权限
    '_child': [
    {
    'id': 2,
    'parent_id': 1,
    'menu_code': 'M1003',
    'isHaveRole': 0
    },
    {
    'id': 3,
    'parent_id': 1,
    'menu_code': 'M1004',
    'isHaveRole': 0
    }
    ]
    }
    ]
    },

    ]
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
    public function getAccountInfo(Request $request,$id)
    {
        //先判断所要编辑的子账户是否存在
        $findSub = Agent::where(['is_hall_sub'=>1,'account_state'=>1])->find($id);
        if(!$findSub)
        {
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('role.data_error'),
                'result'    => ''
            ]);
        }

        //获取角色分组信息
        $roleGroupList = RoleGroup::where('state',1)->where(['agent_id'=>$this->agentId])->select('id','group_name')->get()->toArray();
        if(!$roleGroupList)
        {
            //角色分组不存在，返回错误信息
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('role.empty_list'),
                'result'    => ''
            ]);
        }
        //获取子账户的原先权限菜单信息
//        $accountRoleList = UserMenu::where('user_id',$id)->select('role_id','menu_id','parent_id')->get()->toArray();
//        $accountGroupIdList = array_column($accountRoleList,'role_id');
//        $accountMenuIdList = array_column($accountRoleList,'menu_id');
//        $accountParentMenuIdList = array_column($accountRoleList,'parent_id');
        //获取子账户的原先所属账户组
        $accountGroup = Agent::where('id',$id)->select('group_id')->first()->toArray();

        //获取全部菜单信息
        $user = $this->agentInfo;
        $menusList = Menu::select('id','menu_id','parent_id','menu_code')->where('state',1)->where(['grade_id'=>$user->grade_id])->get()->toArray();
        $sysMenus = DB::table('agent_system_menus')->orderBy('sort_id')->where('state',1)->get()->toArray();
        //根据分组信息获取对应的权限信息
        foreach ($roleGroupList as $k=>$v)
        {
            //获取到对应分组权限
            $subRoleMenus = RoleGroupMenu::where('role_id',$v['id'])->get()->toArray();
            $sub_menu_id = array_column($subRoleMenus,'menu_id'); //分组菜单数组
            $sub_parent_id = array_column($subRoleMenus,'parent_id');//菜单所属patent_id 数组
            $subRoleMenusList = [];
            foreach ($menusList as $men_k=> $menu)
            {
                if(in_array($menu['menu_id'],$sub_menu_id) && !in_array($menu['menu_id'],$sub_parent_id))
                {
                    $subRoleMenusList[] = $menu;
                }
            }
            foreach ($sysMenus as $kk=>$vv)
            {
                foreach (array_unique($sub_parent_id) as  $k2=>$v2)
                {
                    if($vv->id==$v2)
                    {
                        $subRoleMenusList[] = [
                            'id'    => $vv->id,
                            'menu_id'   => $vv->id,
                            'parent_id' => $vv->parent_id,
                            'menu_code' => $vv->menu_code,
                        ];
                    }
                }
            }

            if(!empty($subRoleMenusList))
            {
                foreach ($subRoleMenusList as $k1=>$v1)
                {
                    //((in_array($v1['menu_id'],$accountMenuIdList) || in_array($v1['menu_id'],$accountParentMenuIdList)) && in_array($v['id'],$accountGroupIdList)) ? $subRoleMenusList[$k1]['isHaveRole'] = 1 : $subRoleMenusList[$k1]['isHaveRole'] = 0;
                    $v['id'] == $accountGroup['group_id'] ? $subRoleMenusList[$k1]['isHaveRole'] = 1 : $subRoleMenusList[$k1]['isHaveRole'] = 0;
                }
            }
            $v['id'] == $accountGroup['group_id'] ? $roleGroupList[$k]['isHaveRole'] = 1 : $roleGroupList[$k]['isHaveRole'] = 0;
            $roleGroupList[$k]['roles'] = get_attr($subRoleMenusList,'0');
            unset($subRoleMenusList);
        }

        foreach ($roleGroupList as $kk=>$vv)
        {
            if(!isset($vv['isHaveRole']))
            {
                $roleGroupList[$kk]['isHaveRole'] = 0;
            }
        }

        if(!$roleGroupList)
        {
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('role.empty_list'),
                'result'    => ''
            ]);
        }


       // 添加操作日志
        @addLog([
            'action_name'=>' 编辑保存账户权限信息',
            'action_desc'=> " 编辑子账户权限信息; 子账户名称{$findSub->user_name} ID{$id}",
            'action_passivity'=>'用户组权限表'
        ]);

        //返回分组和对应菜单信息
        return $this->response()->array([
            'code'  => 0,
            'text'  => trans('role.success'),
            'result'    => $roleGroupList
        ]);
    }


    /**
     * consumes={"multipart/form-data"},
     * @SWG\Patch(
     *   path="/role/account/menus/{id}",
     *   tags={"系统管理"},
     *   summary="厅主编辑保持子账户权限信息",
     *   description="
     *   厅主编辑保持子账户权限信息
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
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="group_id",
     *     type="string",
     *     description="所属角色分组ID",
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
    public function updateAccount(Request $request, $id)
    {
        //先判断所要编辑的子账户是否存在
        $findSub = Agent::where(['is_hall_sub'=>1,'account_state'=>1])->find($id);
        if(!$findSub)
        {
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('role.data_error'),
                'result'    => ''
            ]);
        }

        //进行数据提交验证
        $messages = [
            'group_id.required'    => trans('role.group.required')
        ];

        $validator = \Validator::make($request->input(),[
            'group_id' => 'required|integer'
        ],$messages);

        //提交数据格式验证错误
        if($validator->fails())
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => $validator->errors()->first(),
                'result'    => ''
            ]);
        }
        $update = Agent::where(['id' => $id])->update(['group_id' => $request->input('group_id')]);

        // 添加操作日志
        @addLog([
            'action_name' => ' 编辑保存子账户权限信息',
            'action_desc' => " 编辑保持子账户权限信息; 名称{$findSub->username_md} ID{$id}",
            'action_passivity' => '用户权限组'
        ]);
        if(!$update)
        {
            return $this->response()->array([
                'code' => 400,
                'text' => trans('role.fails'),
                'result' => ''
            ]);
        }

        //写入成功返回成功信息
        return $this->response()->array([
            'code' => 0,
            'text' => trans('role.success'),
            'result' => ''
        ]);

    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Get(
     *   path="/getAgentMenus",
     *   tags={"厅主查看代理所有权限"},
     *   summary="厅主查看代理所有权限",
     *   description="
     *   厅主查看代理所有权限
     *   成功返回字段说明
    {
    'code': 0,
    'text': '操作成功',
    'result': [
    {
    'id': 1,
    'parent_id': 0,
    'title_cn': '账号管理',
    'title_en': '',
    'class': 0,
    'desc': null,
    'link_url': '/accountManage',
    'icon': 'icon-guanli',
    'state': 1,
    'sort_id': 1,
    'menu_code': 'M1001',
    'update_date': null,
    '_child': [
    {
    'id': 780,
    'menu_id': 3,
    'grade_id': 2,
    'parent_id': 1,
    'title_cn': '玩家管理',
    'title_en': '',
    'class': 0,
    'desc': null,
    'link_url': '/accountManage/PlayerM',
    'icon': 'icon-youxi1',
    'state': 1,
    'sort_id': 1,
    'menu_code': 'M1004'
    },
    {
    'id': 781,
    'menu_id': 5,
    'grade_id': 2,
    'parent_id': 1,
    'title_cn': '代理维护',
    'title_en': '',
    'class': 0,
    'desc': null,
    'link_url': '/accountManage/AgentMain',
    'icon': 'icon-dailichengyuanguanli',
    'state': 1,
    'sort_id': 1,
    'menu_code': 'M1011'
    }
    ]
    },
    ]
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
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="group_id",
     *     type="string",
     *     description="所属角色分组ID",
     *     required=true,
     *     default="1"
     *   ),
     *   operationId="captcha",
     *   @SWG\Parameter(
     *     in="header",
     *     name="roles[]",
     *     type="string",
     *     description="菜单数据",
     *     required=true,
     *     default=""
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
    public function getAgentMenusList()
    {
        //获取所有的厅主系统菜单数据
        $menusList = DB::table('agent_system_menus')->where('state','=',1)->whereIn('class',[2,0])->get()->toArray();
        //获取总平台分配给代理商类型的菜单
        $agentMenusList = DB::table('agent_menus_list')->where('state','=',1)->where('grade_id',2)->get()->toArray();
        if(!$menusList || !$agentMenusList)
        {
            return  $this->response()->array([
                'code'          => 400,
                'text'          => trans('delivery.empty_list'),
                'result'        => ''
            ]);
        }

        //进行数据组装
        $mList = array_unique(array_column($agentMenusList,'parent_id'));
        $pList = [];
        foreach ($menusList as $key=>&$val)
        {
            $val = json_decode(json_encode($val),true);
            if(in_array($val['id'],$mList))
            {
                $pList [] = $val;
            }

        }
        $newList = array_merge($pList,$agentMenusList);

        foreach ($newList as &$v)
        {
            $v = json_decode(json_encode($v),true);
        }
        return  $this->response()->array([
            'code'          => 0,
            'text'          => trans('delivery.success'),
            'result'        => list_to_tree($newList,'id','parent_id')
        ]);
    }
}