<?php
/**
 * Created by PhpStorm.
 * User: chengkang
 * Date: 2017/2/5
 * Time: 18:00
 * Desc: 用户下注明细
 */
namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
class PlayerOrder extends Eloquent
{
    use  Authenticatable;
    public $timestamps = false; //关闭创建时间、更新时间
    protected $connection = 'mongodb';
    protected $table = 'user_order';

}