<?php


namespace App\Api\Helpers;


use AlibabaCloud\Client\AlibabaCloud;
use Illuminate\Support\Facades\Redis;

class AliyunSmsHandle
{
    protected static $client_name = 'aliyun_sms';

    public function __construct()
    {
        AlibabaCloud::accessKeyClient(
            config('services.aliyun.sms.access_key_id'),
            config('services.aliyun.sms.access_key_secret')
        )
            ->regionId('cn-hangzhou')
            ->name(self::$client_name);
    }

    /**
     * 发送短信
     * @param array $phones
     * @param array $params
     * @param $template_id
     * @param string $sign
     * @throws \AlibabaCloud\Client\Exception\ClientException
     * @author onetobig
     * @date 2020-09-10 17:43
     */
    public function sendSms(array $phones, array $params, $template_id, $sign = '校天犬')
    {
        $phones = collect($phones)->filter()->unique();
        if ($phones->count() <= 0) {
            return true;
        }
        try {
            AlibabaCloud::rpc()
                ->client(self::$client_name)
                ->product('Dysmsapi')
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->host('dysmsapi.aliyuncs.com')
                ->options([
                    'query' => [
                        'RegionId' => "cn-hangzhou",
                        'PhoneNumbers' => $phones->implode(','),
                        'SignName' => $sign,
                        'TemplateCode' => $template_id,
                        'TemplateParam' => \json_encode($params, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES),
                    ]
                ])
                ->request();
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * 发送验证码短信
     * @author onetobig
     * @date 2020-09-10 17:44
     */
    public function sendVerifyCode(array $phones, $code)
    {
        return $this->sendSms($phones, ['code' => $code], 'SMS_198691072');
    }

    /**
     * @author onetobig
     * @date 2020-09-17 9:54
     */
    public function checkSmsRate($phone, $seconds = 60)
    {
        $key = 'sms:phone:rates';
        $time = Redis::hGet($key, $phone);
        Redis::hSet($key, $phone, time());
        if ($time && time() - $time < $seconds) {
            error_msg("获取短信太频繁，请稍后再试");
        }
        return true;
    }

}
