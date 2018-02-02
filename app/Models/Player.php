<?php
/**
 * Created by PhpStorm.
 * User: chengkang
 * Date: 2017/2/5
 * Time: 18:00
 * Desc: 玩家模型
 */
namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class Player extends BaseModel
{
    use  Authenticatable;
    protected $table = 'lb_user';
    protected $hidden = [];
    protected $primaryKey = 'uid';
    public $timestamps = false; //关闭创建时间、更新时间

}