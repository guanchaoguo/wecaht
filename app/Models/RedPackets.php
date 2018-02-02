<?php
namespace App\Models;

use Illuminate\Auth\Authenticatable;


class RedPackets extends BaseModel
{
    use  Authenticatable;
    protected $table = 'activity_red_packets';
    protected $hidden = ['create_date','last_date'];
    public $timestamps = false; //关闭创建时间、更新时间

}