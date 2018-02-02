<?php
/**
 * 文案logo.
 * User: chensongjian
 * Date: 2017/4/13
 * Time: 9:42
 */

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class GamePlatformLogo extends BaseModel
{
    use  Authenticatable;
    protected $table = 'game_platform_logo';
    protected $hidden = [];
//    public $timestamps = false; //关闭创建时间、更新时间
    const UPDATED_AT='update_date';
    const CREATED_AT = 'add_date';
}