<?php
/**
 * Created by PhpStorm
 * User: Alon
 * Date: 2021/10/15
 * Time: 1:44 下午
 */

namespace App\Services\TencentCloud;

use App\Enums\Sms\SmsType;
use Illuminate\Support\Facades\Redis;
use Qcloud\Sms\SmsSingleSender;

class SmsService
{
    //验证码(验证码为{1}，{1}分钟内有效，如非本人操作请忽略本短信；为了保障您的账户安全，请勿向他人泄露验证码信息。)
    const WARNING_CODE = '1703367';

	/**
	 * @param $phone
	 * @param SmsType $type
	 * @param int $expired
	 * @return void
	 */
	public static function sendSmsCode($phone, SmsType $type, int $expired = 5): void
    {
        $suffix = $type->getValue();
        $template = self::WARNING_CODE;
        $bool = self::canSendCode($phone, $suffix);
		if (!$bool) {
			abort(403, '短信发送频繁,请稍后重试');
		}
        try {
            $randomCode = config('app.env') == 'local' ? '1234' : random_int(1000, 9999);
            self::setCacheCode($phone, $randomCode, $expired, $suffix);
            self::send($phone,$template,[$randomCode,$expired],config('tencent.sign_name'));
        }catch (\Exception $exception){
            abort(500, '短信发送失败');
        }
	}

	/**
	 * @param $phone
	 * @param $smsCode
	 * @param SmsType $type
	 * @return bool
	 */
    public static function validateSmsCode($phone, $smsCode, SmsType $type): bool
    {
        $suffix = $type->getValue();
        $code = config('app.env') == 'local' ? '1234' : Redis::connection()->client()->get($phone . $suffix . '-v');
        if ($smsCode == $code) {
            Redis::connection()->client()->del($phone . $suffix);
            Redis::connection()->client()->del($phone . $suffix . '-v');
            return true;
        }
        return false;
    }

	/**
	 * @throws \Exception
	 */
	protected static function send(string $phone, string $template, array $content, $signName = '清宁云服')
	{
        if(empty($template)){
            abort(404, '短信模板未配置');
        }
		if (config('app.env') == 'local') {
			info('测试环境短信模拟发送',['phone' => $phone,'template' => $template, 'content' => $content]);
			return;
		}
		try {
			// 短信应用SDK AppID
			$appId = config('tencent.sdk_app_id'); // 1400开头
			// 短信应用SDK AppKey
			$appKey = config('tencent.app_key');
			$smsSign = $signName;
			$sender = new SmsSingleSender($appId, $appKey);
			info('腾讯短信', ['phone' => $phone, 'content' => $content, 'sign_name' => $smsSign]);
			$sender->sendWithParam("86", $phone, $template, $content, $smsSign, "", "");  // 签名参数不能为空串
		} catch (\Exception $e) {
			error_log('腾讯短信', $e->getMessage());
			throw new $e;
		}
	}

	protected static function setCacheCode($phone, $code, $expired, $suffix)
	{

        $key = $phone . $suffix;
        if (is_null(Redis::connection()->client()->get($key))) {
            Redis::connection()->client()->setex($key, 60, 1);
        } else {
            Redis::connection()->client()->incr($key);
        }
        Redis::connection()->client()->setex($key.'-v', $expired * 60, $code);
	}

	protected static function canSendCode($phone,$suffix): bool
	{
        $can = Redis::connection()->client()->get($phone . $suffix) ?? 0;
        return $can < 3;
	}
}
