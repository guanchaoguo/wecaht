<?php
/**
 * Created by PhpStorm.
 * User: chengkang
 * Date: 2017/2/8
 * Time: 17:38
 */
namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class UploadController extends BaseController
{
    public function __construct()
    {
    }


    /**
     * consumes={"multipart/form-data"},
     * @SWG\Post(
     *   path="http://images.dev/upload.php",
     *   tags={"文件"},
     *   summary="文件上传",
     *   @SWG\Parameter(
     *     in="formData",
     *     name="file",
     *     type="string",
     *     description="上传控件名称",
     *     required=true,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="type",
     *     type="string",
     *     description="文件类型，1表示文档，否则是图片",
     *     required=false,
     *     default="1"
     *   ),
     *   description="
     *   文件上传接口
     *   使用文件上传域名： http://images.dev/
     *   成功返回字段说明
        {
        'code':0,
        'text':'success',
        'result':
        [
        {
        'host':'http://images.dev/',//域名
        'save_path':'./upload/2017/05/2017052505071153.jpg',//保存数据库的路径
        'size':'41.6KB'//文件大小
        }
     *
        ]
        }",
     *   operationId="upload",
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
    public function index(Request $request)
    {

        return $this->response->array([
            'code' => 400,
            'text' =>trans('agent.fails'),
            'result' => '请访问文件域名：'.env('IMAGE_HOST')

        ]);
        $file = $request->file('file');
        $ext = $file->getClientOriginalExtension();     // 扩展名
        $pathname = 'images/';
        $filename = date('Y-m-d-H-i-s') . '-' . uniqid() . '.' . $ext;
        $file->move($pathname, $filename);
        return $this->response->array([
            'code' => 0,
            'text' =>trans('agent.success'),
            'result' => [
                'filename' => $pathname.$filename,
                'host' => env('APP_HOST'),
            ],
        ]);
    }


    /**
     * consumes={"multipart/form-data"},
     * @SWG\Post(
     *   path="http://images.dev/getImages.php",
     *   tags={"文件"},
     *   summary="获取图片缩略图",
     *   description="
     * 使用图片服务器域名：http://images.dev/
     *   成功返回字段说明
    {
    'code': 0,
    'text': 'success',
    'result': [
    {
    '500_200': './upload/2017/05/thumb_500_200_2017052505071153.jpg'
    },
    {
    '800_500': './upload/2017/05/thumb_800_500_2017052505071153.jpg'
    }
    ]
    }",
     *   operationId="upload",
     *   @SWG\Parameter(
     *     in="formData",
     *     name="file",
     *     type="string",
     *     description="原图片路径",
     *     required=true,
     *     default=""
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="size",
     *     type="string",
     *     description="要生成的图片尺寸 数组 格式['500_200','800_500']",
     *     required=true,
     *     default=""
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */

    //删除方法没有用到
    public function delete(Request $request) {

        return $this->response->array([
            'code' => 400,
            'text' =>trans('agent.fails'),
            'result' => '请访问文件域名：'.env('IMAGE_HOST')

        ]);
        $path = $request->input('path');
        if( ! File::exists( $path)) {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.file_not_eixt'),
                'result' => $path

            ]);

        }

        if( ! File::delete($path) ) {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.fails'),
                'result' => $path

            ]);

        } else {
            return $this->response->array([
                'code' => 0,
                'text' =>trans('agent.success'),
                'result' =>''

            ]);
        }

    }

    /**
     * consumes={"multipart/form-data"},
     * @SWG\Post(
     *   path="http://images.dev/removeFile.php",
     *   tags={"文件"},
     *   summary="删除文件",
     *   description="
     * 使用文件域名：http://images.dev/
     *   成功返回字段说明
    {
    'code': 0,
    'text': 'success',
    'result': ''
    }",
     *   operationId="upload",
     *   @SWG\Parameter(
     *     in="formData",
     *     name="file",
     *     type="string",
     *     description="文件路径（相对路径，数据库保存的路径）",
     *     required=true,
     *     default=""
     *   ),
     *   @SWG\Response(response="200",
     *     description="Success"
     * ),
     * )
     */
}