<?php
return [
    'alipay' => [
        'app_id'         => '2016091300503332',
        'ali_public_key' => '',
        'private_key'    => '',
        'log'            => [
            'file' => storage_path('logs/alipay.log'),
        ],
    ],

    'wechat' => [
        'miniapp_id'      => env('WECHAT_PAYMENT_APPID'),
        'mch_id'      => env('WECHAT_PAYMENT_MCH_ID'),
        'key'         => env('WECHAT_PAYMENT_KEY'),
        'cert_client' => storage_path('apiclient_cert.pem'), // optional，退款等情况时用到
        'cert_key' => storage_path('apiclient_key.pem'),// optional，退款等情况时用到
        'log' => [ // optional
            'file' => storage_path('/logs/wechat_pay.log'),
            'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
            'type' => 'daily', // optional, 可选 daily.
            'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
        ],
        'http' => [ // optional
            'timeout' => 10.0,
            'connect_timeout' => 10.0,
            // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
        ],
    ],
];
