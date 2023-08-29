<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Services\Tencent\CosService;
use App\Services\TencentCloud\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class FileController extends Controller
{
    //允许上传的文件类型
    protected array $extensionWhiteList = [
        'jpg',
        'jpeg',
        'png',
        'bmp',
        'pdf',
        'ico',
        'xls',
        'xlsx',
        'zip',
        'tar',
        'gz',
        '7z',
        'rar',
        'doc',
        'docx',
        'txt',
        'csv',
        'mp4',
        'avi',
        'rmvb',
        'flv',
        'mov',
        'wmv',
        'pem'
    ];

    /**
     *
     */
    public function upload(Request $request): \Illuminate\Http\JsonResponse | array
    {
        ini_set('memory_limit','1024M');
        if (!$request->file('file')) {
            abort(400,'文件必传');
        }
        $file = $request->file('file');
        $originalName = filter_special($file->getClientOriginalName());
        $fileName = uniqid() . '_&_' .$originalName;
        $extension = $file->getClientOriginalExtension();
        if (!in_array(strtolower($extension), $this->extensionWhiteList)) {
            abort(400,'文件格式非法');
        }
        $size = $file->getSize();
        $maxSize = config('filesystems.max_upload_size');
        if ($size > $maxSize) {
            abort(400,'超出最大文件上传限制,请压缩后重试');
        }

        //存储在腾讯云
        Storage::disk('cos')->putFileAs(config('app.env'), $file, $fileName);
        $path = Storage::disk('cos')->url(config('app.env').'/'.$fileName);

        return $this->success([
            'path' => $path,
        ]);

    }

}
