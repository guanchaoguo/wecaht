<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkj.com
 * Date: 2017/3/29
 * Time: 16:32
 * 发送邮件定时任务
 */

namespace App\Jobs;
use Illuminate\Support\Facades\Mail;

class SendEmailJob extends  Job
{
    /**
     * 创建一个新的任务实例。
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * 运行任务。
     *
     * @return void
     */
    public function handle()
    {
        //
        $flag =  Mail::raw('这是一封测试邮件', function ($message) {
            $to = '2229434675@qq.com';
            $message ->to($to)->subject('测试邮件');
        });
        if($flag){
            echo '发送邮件成功，请查收！';
        }else{
            echo '发送邮件失败，请重试！';
        }
    }
}