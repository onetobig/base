<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/14
 * Time: 14:42
 */

namespace App\Admin\Controllers;


use App\Handlers\ImageUploadHandler;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UploadsController extends Controller
{
    public function store(ImageUploadHandler $uploader, Request $request)
    {
        // 初始化返回数据
        $data = [
            'errno'   => 1,
            'msg'       => '上传失败',
            'data' => [],
        ];
        // 判断是否有上传文件，并复制给 $file
        if($files = $request->upload_file){
            foreach ($files as $file) {
                // 保存图片到本地
                $result = $uploader->save($file, 'topics', \Auth::id(), 1024);
                // 图片保存成功的话
                if($result){
                    $data['data'][] = $result['path'];
                    $data['msg']       = '上传成功!';
                    $data['errno']   = 0;
                }
            }
        }
        return $data;
    }
}
