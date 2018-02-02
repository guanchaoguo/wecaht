<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkj.com
 * Date: 2017/7/15
 * Time: 13:56
 * 判断用户是否拥有某个菜单ID中间件
 */
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MenuMiddleware
{
    public function handle($request, Closure $next)
    {

       $menuId = $request['menu_id'];
       $group_id =  Auth::user()->group_id;
        $user_id = Auth::user()->id;
        $is_hall_sub = Auth::user()->is_hall_sub;
        $grade_id = Auth::user()->grade_id;
        if ($is_hall_sub)
        {//子账号
            //判断用户是否拥有该权限
            $userMenus = DB::table('agent_role_group_menus')->where('role_id',$group_id)->where('menu_id',$menuId)->first();
        }else{
            //厅主和代理
            $userMenus = DB::table('agent_menus_list')->where('menu_id',$menuId)->where('grade_id',$grade_id)->first();
        }

        if(!$userMenus)
        {
            return response()->json([
                'code' => 400,
                'text' => trans('auth203.no_permission')
            ]);
        }
        return $next($request);
    }
}