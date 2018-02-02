<?php
namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Apilog extends Eloquent
{
    protected $table = 'agent_operation_log';
    protected $connection = 'mongodb';
    protected $hidden = ['_id'];
    public $timestamps = false; //关闭创建时间、更新时间
}