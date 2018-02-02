<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class Agent extends BaseModel implements AuthenticatableContract, JWTSubject
{
    use  Authenticatable;
    protected $table = 'lb_agent_user';
    protected $hidden = ['password'];
    public $timestamps = false; //关闭创建时间、更新时间

    // jwt 需要实现的方法
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    // jwt 需要实现的方法
    public function getJWTCustomClaims()
    {
        return [];
    }

    //厅主所属的游戏厅下的游戏(已删除的游戏不显示)
    public function hallGames()
    {
        return $this->hasMany(AgentGame::class, 'agent_id', 'id')->where('game_info.status','<>', 2)->join('game_info','agent_game.game_id','=','game_info.id')->join('game_hall','agent_game.hall_id','=','game_hall.id')->select('game_id','game_info.game_name','game_info.status as game_sta','hall_id','game_hall.game_hall_code','agent_game.status');
    }

    //用户权限
    public function roles()
    {
        return $this->belongsToMany(Menu::class, 'agent_menus', 'user_id', 'menu_id');
    }

    public function roleGroup() {
        return $this->belongsTo(RoleGroup::class, 'group_id','id')->select('group_name');
    }
}
