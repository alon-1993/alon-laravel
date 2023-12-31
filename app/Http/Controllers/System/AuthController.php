<?php

namespace App\Http\Controllers\System;


use App\Enums\Sms\SmsType;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\PersonalAccessToken;
use App\Services\TencentCloud\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * 运营端用户登陆
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $data = $request->all();
        $rule = [
            'account' => 'required',
            'sms_code' => 'required_if:is_password,false',
            'password' => 'required_if:is_password,true',
            'is_password' => 'nullable|boolean',
            'captcha_key' => 'required',
            'captcha' => 'required|captcha_api:' . $data['captcha_key']
        ];

        $this->validate($request, $rule, [
            'account.required' => '账号必填',
            'captcha_key.required' => '验证码key必填',
            'captcha.required' => '图形验证码必填',
            'captcha.captcha_api' => '图形验证码错误',
            'sms_code.required_if' => '短信验证码错误',
            'password.required_if' => '密码错误',
        ]);

        $admin = $this->validateAdmin($request);

        if($request->get('is_password') === true){
            if(!password_verify($request->get('password'), $admin->getAttribute('password'))){
                abort(400, '密码错误');
            }
        }

        auth()->login($admin);

        if (admin()->isDisabled()) {
            abort(403, '账号已禁用,请联系管理员解禁');
        }

        if ($request->get('is_password') !== true && !SmsService::validateSmsCode(admin()->getPhone(), $data['sms_code'], SmsType::from('login'))) {
            abort(400, '短信验证码错误');
        }

        return $this->loginSucceeded();

    }

    public function loginSucceeded(): JsonResponse
    {
        PersonalAccessToken::handleOldToken();
        $token = admin()->createToken('qb-token')->plainTextToken;

        return $this->success([
            'token' => $token,
            'admin' => admin(),
        ]);
    }

    /**
     * 退出登陆
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success();
    }

    protected function validateAdmin(Request $request)
    {
        $account = $request->get('account');
        $organization = Organization::getOrganization($request->header('host'));
        if(empty($organization)){
            abort(404,'机构不存在');
        }
        $admin = Admin::query()
            ->where('organization_id', '=', $organization->getKey())
            ->where(function ($query) use ($account) {
            return $query->where('phone', '=', $account)
                ->orWhere('name', '=', $account);
        })->first();

        if (empty($admin)) {
            abort(404, '账户不存在');
        }
        return $admin;
    }
}
