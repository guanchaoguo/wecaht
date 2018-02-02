<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
 */

$app->get('/', function () use ($app) {
    return 'agentSystem Api';
    return $app->version();
});
$app->get('test',[
    'uses' =>'TestController@index'
]);

$app->get('/doc', [
    'uses' =>'V1\SwaggerController@doc',
]);

$api = app('Dingo\Api\Routing\Router');

// admin_v1 version API
// choose version add this in header    Accept:application/vnd.agent.v1+json
$api->version(['v1'], ['namespace' => 'App\Http\Controllers\V1','middleware' => 'locale'], function ($api) {

    //图片上传
    $api->post('upload',[
        'as' => 'file.upload',
        'uses' =>'UploadController@index',
    ]);

    //删除文件
    $api->delete('file', [
        'as' => 'file.delete',
        'uses' => 'UploadController@delete',
    ]);

    // Captcha 验证码
    $api->post('captcha', [
        'as' => 'captcha.index',
        'uses' => 'CaptchaController@index',
    ]);


    // Auth login 登录认证
    $api->post('authorization', [
        'as' => 'auth.login',
        'uses' => 'AuthController@login',
    ]);


    // AUTH
    // refresh jwt token
    $api->post('auth/token/new', [
        'as' => 'auth.token.new',
        'uses' => 'AuthController@refreshToken',
    ]);

    //找回密码操作(发送邮件)
    $api->post('auth/getPwd',[
        'as'    => 'password.retrievePwd',
        'uses'  => 'PasswordController@retrievePwd'
    ]);
    //根据邮箱验证码进行修改密码操作
    $api->patch('auth/emailPwd',[
        'as'        => 'password.emailEditPwd',
        'uses'      => 'PasswordController@emailEditPwd'
    ]);


    // need authentication
    $api->group(['middleware' => 'auth:admin'], function ($api) {

        //需要经过权限验证的操作接口
        $api->group(['middleware' => 'menu'],function ($api){
            //修改玩家密码
            $api->patch('player/{id}/password', [
                'as' => 'player.password',
                'uses' => 'PlayerController@password',
            ]);
            //修改玩家余额
            $api->patch('player/{id}/balance', [
                'as' => 'player.balance.update',
                'uses' => 'PlayerController@balanceUpdate',
            ]);


            //保存代理商菜单权限
            $api->post('/agent/{agent_id}/menus',[
                'uses'   => 'AgentController@setMenuRole'
            ]);

            //修改手机&邮箱
            $api->patch('/agent/{agent_id}/emailTel',[
                'uses'   => 'AgentController@setEmailTel'
            ]);

            //修改锁定状态&原因
            $api->patch('/agent/{agent_id}/locking',[
                'uses'   => 'AgentController@setLock'
            ]);

            //游戏厅限额添加
            $api->post('hall/quota', [
                'as' => 'hall.store',
                'uses' => 'HallQuotaController@store',
            ]);
            //游戏厅限额保存
            $api->put('hall/quota/{id}', [
                'as' => 'hall.update',
                'uses' => 'HallQuotaController@update',
            ]);
            //快捷设定限额（添加）
            $api->post('hall/quota/shortcut', [
                'as' => 'hall.shortcutStore',
                'uses' => 'HallQuotaController@shortcutStore',
            ]);
            //快捷设定限额（保存）
            $api->put('hall/quota/shortcut/{id}', [
                'as' => 'hall.shortcutUpdate',
                'uses' => 'HallQuotaController@shortcutUpdate',
            ]);
        });

        //修改玩家状态（启用，冻结，停用）
        $api->patch('player/{id}/status', [
            'as' => 'player.status.update',
            'uses' => 'PlayerController@statusUpdate',
        ]);

        //正常修改密码操作
        $api->patch('auth/editPwd/{id}',[
            'as'        => 'password.editPwd',
            'uses'      => 'PasswordController@editPwd'
        ]);
        /**
         * 玩家管理start
         */
        //玩家列表
        $api->get('player',[
            'as' => 'player.index',
            'uses' => 'PlayerController@index',
        ]);
        //添加玩家
        $api->post('player', [
            'as' => 'player.store',
            'uses' => 'PlayerController@store',
        ]);
        //保存玩家
        $api->put('player/{id}', [
            'as' => 'player.update',
            'uses' => 'PlayerController@update',
        ]);
        //获取玩家单条数据
        $api->get('player/{id}', [
            'as' => 'player.show',
            'uses' => 'PlayerController@show',
        ]);
        //查询玩家余额（共享钱包）
        $api->get('player/{user_id}/getUserBalance', [
            'as' => 'player.getUserBalance',
            'uses' => 'PlayerController@getUserBalance',
        ]);


        //玩家登出
        $api->patch('/player/{id}/onLine', [
            'as' => 'player.signOut',
            'uses' => 'PlayerController@signOut',
        ]);

        //玩家下注查询
        $api->post('player/order', [
            'as' => 'player.order',
            'uses' => 'PlayerController@userChartInfo',
        ]);
        //注单查询 游戏结果
//        $api->get('player/order/{_id}',[
//            'as' => 'player.showOrder',
//            'uses' => 'PlayerController@showOrder'
//        ]);

        //注单查询 游戏结果
        $api->get('/player/order/{account}/{round_no}',[
            'uses' => 'PlayerController@showOrderDetail'
        ]);

        /**
         * 玩家管理end
         */

        /**
         * 游戏管理start
         */

        //修改游戏状态（1显示，0不显示）
        $api->patch('hall/game/{game_id}/status', [
            'as' => 'hall.game.status.update',
            'uses' => 'HallGameController@statusUpdate',
        ]);

        //保存游戏显示状态（批量）
        $api->put('hall/game/status', [
            'uses' => 'HallGameController@batchUpdateStatus',
        ]);

        //游戏厅
        $api->get('gameHall', [
            'as' => 'game.gameHall',
            'uses' => 'HallGameController@gameHall',
        ]);
        //游戏分组列表
        $api->get('hall/game', [
            'as' => 'hall.game.index',
            'uses' => 'HallGameController@index',
        ]);

        //游戏列表
        $api->get('game', [
            'as' => 'game.games',
            'uses' => 'HallGameController@games',
        ]);



        //游戏厅限额查询
        $api->get('hall/quota', [
            'as' => 'hall.quota',
            'uses' => 'HallQuotaController@index',
        ]);

        /**
         * 游戏管理end
         */

        /**
         * 查询现金流start
         */

        $api->get('cashRecord', [
            'as' => 'cashRecord.index',
            'uses' => 'CashRecordController@index',
        ]);

        /**
         * 查询现金流end
         */

        /**
         * 报表统计start
         */
        //查询总投注额
        $api->get('totalBet', [
            'as' => 'gameStatistics.totalBet',
            'uses' => 'GameStatisticsController@totalBet',
        ]);

        //查询指定代理
        $api->get('totalBet/agent', [
            'as' => 'gameStatistics.totalBet.agent',
            'uses' => 'GameStatisticsController@agentTotalBet',
        ]);

        //查询指定玩家
        $api->get('totalBet/player', [
            'as' => 'gameStatistics.totalBet.player',
            'uses' => 'GameStatisticsController@playerTotalBet',
        ]);
        //查询游戏
        $api->get('totalBet/game', [
            'as' => 'gameStatistics.totalBet.game',
            'uses' => 'GameStatisticsController@gameTotalBet',
        ]);
        /**
         * 报表统计end
         */
        /**
         * 首页统计start
         */

        //今日统计
        $api->get('statistics/today', [
            'as' => 'statistics.today.data',
            'uses' => 'HomeController@getTodayData',
        ]);

        //今日注单数、今日派彩总额、今日投注总额
        $api->get('statistics/today/moneyQuantity', [
            'uses' => 'HomeController@getTodayMoneyQuantity',
        ]);

        //会员总数、一小时活跃玩家数、当前游戏玩家数
        $api->get('statistics/today/user', [
            'uses' => 'HomeController@getTodayUser',
        ]);
        //厅主平台-代理盈利排行
        $api->get('statistics/today/agent/top10', [
            'uses' => 'HomeController@getAgentWinScoreTop10',
        ]);
        //厅主平台-活跃会员数排名
        $api->get('statistics/today/activeUser/top10', [
            'uses' => 'HomeController@getActiveUserTop10',
        ]);
        //代理平台-会员盈利排行
        $api->get('statistics/today/user/score/top10', [
            'uses' => 'HomeController@getUsertWinScoreTop10',
        ]);
        //代理平台-会员注单数数排名
        $api->get('statistics/today/user/countScore/top10', [
            'uses' => 'HomeController@getUsertCountScoreTop10',
        ]);

        //本周、上周统计
        $api->get('statistics/week', [
            'as' => 'statistics.week',
            'uses' => 'HomeController@getWeekData',
        ]);
        //周（月）统计（用户）
        $api->get('statistics/user', [
            'as' => 'statistics.user',
            'uses' => 'HomeController@getDataByUser',
        ]);
        //周（月）统计（金额）
        $api->get('statistics/score', [
            'as' => 'statistics.score',
            'uses' => 'HomeController@getDataByScore',
        ]);

        //近半年统计
        $api->get('statistics/semi-annual', [
            'as' => 'statistics.semiAnnual',
            'uses' => 'HomeController@getSemiAnnualData',
        ]);
        /**
         * 首页统计end
         */
        /**
         * 游戏风格模板start
         */
        //模板列表
        $api->get('gameTemplate',[
            'as' => 'game.template.index',
            'uses' => 'GameTemplateController@index',
        ]);

        //获取模板详情
        $api->get('gameTemplate/{id}',[
            'as' => 'game.template.show',
            'uses' => 'GameTemplateController@show',
        ]);
        /**
         * 游戏风格模板end
         */

        /**
         * 文案LOGO start
         */
        //列表
        $api->get('copywriter/logo',[
            'as' => 'GamePlatformLogo.index',
            'uses' => 'GamePlatformLogoController@index',
        ]);
        //添加
        $api->post('copywriter/logo',[
            'as' => 'GamePlatformLogo.store',
            'uses' => 'GamePlatformLogoController@store',
        ]);
        //详情
        $api->get('copywriter/logo/{id}',[
            'as' => 'GamePlatformLogo.show',
            'uses' => 'GamePlatformLogoController@show',
        ]);
        //编辑保存
        $api->put('copywriter/logo/{id}',[
            'as' => 'GamePlatformLogo.update',
            'uses' => 'GamePlatformLogoController@update',
        ]);

        //启用&禁用
        $api->patch('copywriter/logo/{id}/isUse',[
            'as' => 'GamePlatformLogo.isUse',
            'uses' => 'GamePlatformLogoController@isUse',
        ]);

        //删除
        $api->delete('copywriter/logo/{id}',[
            'as' => 'GamePlatformLogo.delete',
            'uses' => 'GamePlatformLogoController@delete',
        ]);
        /**
         * 文案LOGO end
         */

        /**
         * 文案Banner start
         */
        //列表
        $api->get('copywriter/banner',[
            'as' => 'GamePlatformBanner.index',
            'uses' => 'GamePlatformBannerController@index',
        ]);
        //添加
        $api->post('copywriter/banner',[
            'as' => 'GamePlatformBanner.store',
            'uses' => 'GamePlatformBannerController@store',
        ]);
        //详情
        $api->get('copywriter/banner/{id}',[
            'as' => 'GamePlatformBanner.show',
            'uses' => 'GamePlatformBannerController@show',
        ]);
        //编辑保存
        $api->put('copywriter/banner/{id}',[
            'as' => 'GamePlatformBanner.update',
            'uses' => 'GamePlatformBannerController@update',
        ]);

        //启用&禁用
        $api->patch('copywriter/banner/{id}/isUse',[
            'as' => 'GamePlatformBanner.isUse',
            'uses' => 'GamePlatformBannerController@isUse',
        ]);

        //排序
        $api->patch('copywriter/banner/{id}/sort',[
            'as' => 'GamePlatformBanner.sort',
            'uses' => 'GamePlatformBannerController@sort',
        ]);

        //删除
        $api->delete('copywriter/banner/{id}',[
            'as' => 'GamePlatformBanner.delete',
            'uses' => 'GamePlatformBannerController@delete',
        ]);
        /**
         * 文案Banner end
         */
        /**
         * 文案活动 start
         */
        //列表
        $api->get('copywriter/activity',[
            'as' => 'GamePlatformActivity.index',
            'uses' => 'GamePlatformActivityController@index',
        ]);
        //添加
        $api->post('copywriter/activity',[
            'as' => 'GamePlatformActivity.store',
            'uses' => 'GamePlatformActivityController@store',
        ]);
        //详情
        $api->get('copywriter/activity/{id}',[
            'as' => 'GamePlatformActivity.show',
            'uses' => 'GamePlatformActivityController@show',
        ]);
        //编辑保存
        $api->put('copywriter/activity/{id}',[
            'as' => 'GamePlatformActivity.update',
            'uses' => 'GamePlatformActivityController@update',
        ]);

        //审核
        $api->patch('copywriter/activity/{id}',[
            'as' => 'GamePlatformActivity.review',
            'uses' => 'GamePlatformActivityController@review',
        ]);

        //删除
        $api->delete('copywriter/activity/{id}',[
            'as' => 'GamePlatformActivity.delete',
            'uses' => 'GamePlatformActivityController@delete',
        ]);
        /**
         * 文案活动 end
         */
        //厅主获取代理商列表
        $api->post('agent',[
            'as'    => 'agent.agentList',
            'uses'  => 'AgentController@agentList'
        ]);
        //厅主添加代理
        $api->post('agent/store',[
            'as'    => 'agent.addAgent',
            'uses'  => 'AgentController@addAgent'
        ]);
        //编辑代理时获取数据
        $api->post('agent/{id}',[
            'as'    => 'agent.getAgent',
           'uses'   => 'AgentController@getAgent'
        ]);
        //编辑保存代理
        $api->patch('agent/{id}',[
            'as'    => 'agent.update',
            'uses'   => 'AgentController@update'
        ]);

        //获取代理商菜单权限列表数据
        $api->get('agent/menu',[
            'uses'   => 'AgentController@getAgentMenu'
        ]);



        //角色组列表
        $api->post('role',[
            'as'    => 'role.index',
            'uses' => 'RoleController@index'
        ]);
        //厅主添加角色分组
        $api->post('role/store',[
            'as'    => 'role.store',
            'uses'  => 'RoleController@store'
        ]);
        //编辑角色分组权限时获取菜单数据
        $api->post('role/menus/{id}',[
            'as'    => 'role.menus',
            'uses'  => 'RoleController@showMenus'
        ]);
        //保存分组角色菜单权限信息
        $api->patch('role/{id}',[
            'as'        => 'role.updateRole',
            'uses'      => 'RoleController@updateRole'
        ]);
        //添加子账号操作
        $api->post('role/account',[
            'as'    => 'role.addAccount',
            'uses'  => 'RoleController@addAccount'
        ]);
        //子账号列表
        $api->post('role/account/list',[
            'as'    => 'role.accountList',
            'uses'  => 'RoleController@accountList'
        ]);
        //修改子账号状态操作
        $api->patch('role/account/state/{id}',[
            'as'        => 'role.accountState',
            'uses'      => 'RoleController@accountState'
        ]);

        //修改子账号密码操作
        $api->patch('role/account/editPwd/{id}',[
            'as'    => 'role.editPwd',
            'uses'  => 'RoleController@accountEditPwd'
        ]);

        //删除角色分组操作
        $api->delete('role/{id}',[
            'as'    => 'role.deleteGroup',
            'uses'  => 'RoleController@deleteGroup'
        ]);
        //编辑子账户权限时获取权限数据
        $api->post('role/account/menus/{id}',[
            'as'    => 'role.showMenus',
            'uses'  => 'RoleController@getAccountInfo'
        ]);
        //编辑保持子账户权限信息
        $api->patch('role/account/menus/{id}',[
            'as'    => 'role.updateMenus',
            'uses'  => 'RoleController@updateAccount'
        ]);

        //交收期数列表
        $api->get('issue',[
            'as'    => 'delivery.index',
            'uses'  => 'DeliveryController@index'
        ]);

        //交收数据列表
        $api->get('delivery',[
            'as'    => 'delivery.issueList',
            'uses'  => 'DeliveryController@issueList'
        ]);
        /**
         * 日志管理start
         */
        //厅主操作日志查看
        $api->get('syslog',[
            'as'    => 'log.syslog',
            'uses'  => 'SyslogController@index'
        ]);
        /**
         * 日志管理end
         */

        //厅主查看代理所有权限
        $api->get('getAgentMenus',[
            'uses'  => 'RoleController@getAgentMenusList'
        ]);

        /**
         * 文档管理start
         */
            //文档管理列表&下载
            $api->get('document', [
                'uses' => 'DocumentController@index'
            ]);
        /**
         * 文档管理end
         */

        /**
         * 电子游戏十三水房间 start
         */
        //电子游戏十三水房间列表显示
        $api->get('room', [
            'as' => 'room.index',
            'uses' => 'RoomInfoController@index',
        ]);

        //电子游戏十三水游戏分类显示
        $api->get('room/cat', [
            'as' => 'room.cat',
            'uses' => 'RoomInfoController@cat',
        ]);

        //电子游戏十三水游戏盈利率显示
        $api->get('room/rules/show/{room_id}', [
            'as' => 'room.showOdds',
            'uses' => 'RoomInfoController@showRules',
        ]);

        //电子游戏十三水房间状态修改
        $api->put('room/status', [
            'as' => 'room.updateStatus',
            'uses' => 'RoomInfoController@updateStatus',
        ]);

        /**
         * 电子游戏十三水房间 end
         */

        //2.3版本路由区块 - 新周

        //风险控制列表
        $api->get('monitor',[
            'uses'  => 'MonitorController@list',
        ]);
        //设置单个监控项参数
        $api->put('monitor',[
            'uses'  => 'MonitorController@setMonitor'
        ]);
        //设置单个监控项的状态
        $api->put('monitor/status',[
            'uses'  => 'MonitorController@setStatus'
        ]);
        //获取报警账号列表操作
        $api->get('alarm/list',[
            'uses'  => 'MonitorController@alarmList'
        ]);
        //添加报警账号操作
        $api->post('alarm',[
            'uses'  => 'MonitorController@addAlarm'
        ]);
        //编辑报警账号时获取信息
        $api->get('alarm',[
            'uses'  => 'MonitorController@getAlarmInfo'
        ]);
        //编辑保存报警账号
        $api->put('alarm',[
            'uses'  => 'MonitorController@updateAlarm'
        ]);
        //删除报警账号操作
        $api->delete('alarm',[
            'uses'  => 'MonitorController@deleteAlarm'
        ]);
        //查看报警记录列表
        $api->get('push/list',[
            'uses'  => 'MonitorController@getPushLog'
        ]);
        //获取监控数据列表
        $api->get("trigger",[
            'uses'  => 'MonitorController@getLog'
        ]);

        //
        //2.3版本路由区块 - 朝国

        /*++++++++++++++++++++++红包活动管理 start +++++++++++++++++++++++++++++*/

        // 厅主红包活动列表
        $api->get('redPackets', ['uses' => 'RedPacketsController@index']);

        // 厅主红包活动列表详情
        $api->get('redPackets/showDetail/{packet_id}', ['uses' => 'RedPacketsController@showDetail']);

        /*++++++++++++++++++++++红包活动管理 start +++++++++++++++++++++++++++++*/

        //
        //2.3版本路由区块 - 松坚

            //厅主公告start
                //获取厅主公告
                $api->get('agent/message', [
                    'uses' => 'AgentMessageController@index',
                ]);
            //厅主公告end

        //

    });



    });
