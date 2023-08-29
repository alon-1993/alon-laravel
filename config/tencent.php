<?php
/**
 * 腾讯云配置
 */
return [
    'sdk_app_id' => env('TENCENT_SMS_APP_ID', ''),
    'app_key' => env('TENCENT_SMS_APP_KEY', ''),
    'sign_name' => env('TENCENT_SMS_SIGN_NAME', ''),
    'ocr' => [
        'secret_id' => env('TENCENT_SECRET_ID',''),
        'secret_key' => env('TENCENT_SECRET_KEY',''),
    ],
];


