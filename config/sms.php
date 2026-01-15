<?php

return [
    'default_provider' => 'log',

    'fallback_providers' => [
        // 'custom_http',
    ],

    'default_country_code' => '86',

    'otp' => [
        'ttl_seconds' => 300,
        'template' => '您的验证码是 {code}，{ttl} 分钟内有效。',
    ],

    'providers' => [
        'log' => [
            'class' => \app\service\sms\LogSmsProvider::class,
            'options' => [
                'channel' => 'default',
            ],
        ],
        'custom_http' => [
            'class' => \app\service\sms\CustomHttpSmsProvider::class,
            'options' => [
                'endpoint' => '',
                'api_key' => '',
                'api_secret' => '',
                'sign' => '',
                'timeout' => 10,
            ],
        ],
        'aliyun' => [
            'class' => \app\service\sms\AliyunSmsProvider::class,
            'options' => [
                'endpoint' => 'https://dysmsapi.aliyuncs.com/',
                'region_id' => 'cn-hangzhou',
                'access_key_id' => '',
                'access_key_secret' => '',
                'sign_name' => '',
                'template_code' => '',
                // 'template_param' => ['code' => '123456'],
                // 'template_param_key' => 'code',
                // 'out_id' => '',
                'timeout' => 10,
            ],
        ],
        'tencentcloud' => [
            'class' => \app\service\sms\TencentCloudSmsProvider::class,
            'options' => [
                'endpoint' => 'sms.tencentcloudapi.com',
                'region' => 'ap-guangzhou',
                'version' => '2021-01-11',
                'secret_id' => '',
                'secret_key' => '',
                'sms_sdk_app_id' => '',
                'sign_name' => '',
                'template_id' => '',
                // 'template_param_set' => ['123456', '5'],
                // 'session_context' => '',
                'timeout' => 10,
            ],
        ],
        'twilio' => [
            'class' => \app\service\sms\TwilioSmsProvider::class,
            'options' => [
                'account_sid' => '',
                'auth_token' => '',
                'from' => '',
                'timeout' => 10,
            ],
        ],
    ],
];
