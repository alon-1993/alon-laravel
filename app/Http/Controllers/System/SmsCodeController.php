<?php

namespace App\Http\Controllers\System;

use App\Enums\Sms\SmsType;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\TencentCloud\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SmsCodeController extends Controller
{

    public function store(Request $request): JsonResponse
    {
        $data = $request->all();

        $this->validate($request, [
            'phone' => 'required',
            'type' => ['required',Rule::in(SmsType::toArray())]
        ], [
            'phone.required' => '手机号必填',
            'type.required' => '短信类型必填',
            'type.in' => '短信类型错误',
        ]);

        if($data['type'] == SmsType::LOGIN()){
            $admin = Admin::query()->where('phone','=',$data['phone'])
                ->first();

            if(empty($admin)){
                abort(404, '账户不存在');
            }
        }
		SmsService::sendSmsCode($data['phone'], SmsType::from($data['type']));
        return $this->success();
    }
}
