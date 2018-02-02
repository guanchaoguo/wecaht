<?php

namespace App\Http\Controllers\V1;

class SwaggerController extends BaseController
{

    public function __construct()
    {
    }

    /**
     * 返回JSON格式的Swagger定义
     * 这里需要一个主`Swagger`定义：
     * @SWG\Swagger(
     *   schemes={"http"},
     *   produces={"Accept:application/vnd.agent.v1+json"},
     *   basePath="/api",
     *   consumes={"Accept:application/vnd.agent.v1+json"},
     *   @SWG\Info(
     *     title="厅主代理后台管理系统API接口文档",
     *     version="1.0",
     *     description="
     *     厅主代理后台管理系统API与前端接口数据通讯。"
     *   ),
     * )
     */
    public function doc()
    {
        // 你可以将API的`Swagger Annotation`写在实现API的代码旁，从而方便维护，
        // `swagger-php`会扫描你定义的目录，自动合并所有定义。这里我们直接用`Controller/`
        // 文件夹。
        $swagger = \Swagger\scan('../app/Http/Controllers/V1');
        return response()->json($swagger, 200);
    }
}
