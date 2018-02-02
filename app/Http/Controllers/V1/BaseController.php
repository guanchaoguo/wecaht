<?php

namespace App\Http\Controllers\V1;

use App\Models\Agent;
use Dingo\Api\Routing\Helpers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class BaseController extends Controller
{

    protected $agentId;
    protected $accountId;
    protected $agentInfo;
    // 接口帮助调用
    use Helpers;

    public function __construct()
    {
        $agentInfo = Auth::user();
        $this->agentInfo = $agentInfo;
        $this->agentId = $agentInfo['id'];
        if($agentInfo['is_hall_sub'] == 1)
        {
            $this->accountId = $agentInfo['id'];
            $this->agentId = $agentInfo['parent_id'];
        }
       /* $this->agentId = Auth::user()->id;
        $find = Agent::where(['id'=>$this->agentId])->first();
        if($find->is_hall_sub == 1)
        {
            $this->accountId = $find->id;
            $this->agentId = $find->parent_id;
        }*/
    }

    // 返回错误的请求
    protected function errorBadRequest($message = '')
    {
        return $this->response->array($message)->setStatusCode(400);
    }
}
