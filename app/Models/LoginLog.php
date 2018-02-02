<?php

namespace App\Models;

/**
登录日志模型
*/
use Illuminate\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class LoginLog extends Eloquent
{
    use  Authenticatable;
    protected $table = 'login_log';
    protected $connection = 'mongodb';
    protected $hidden = ['_id'];
    public $timestamps = false; //关闭创建时间、更新时间

    public function getUserChannel($start_time, $end_time,$sort) {

        return self::raw(function ($collection) use($start_time, $end_time,$sort) {
            return $collection->aggregate([
                [
                    '$match' =>
                        [
                            'add_time' =>
                                [
                                    '$gte' => $start_time,
                                    '$lt' => $end_time,
                                ]

                        ],
                ],
                [
                    '$group' =>
                        [
                            '_id' => '$otype_id',
                            'device_type' => ['$first' =>'$device_type'],
                            'num' => ['$sum'  => 1],
                        ],
                ],
                [
                    '$sort' =>$sort
                ]
            ]);
        });
    }

}
