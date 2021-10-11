<?php

namespace App\Services;

use App\Exceptions\ApiException;
use EasyWeChat\OfficialAccount\Application;
use Overtrue\LaravelWeChat\Facade as EasyWechat;

class WechatOfficialAccountService
{
    protected $app;

    /**
     * 返回小程序对象
     * @return Application
     * @author Administrator
     * @date 2021-04-29 16:18
     */
    public function app($type = 'default')
    {
        if (!$this->app) {
            $this->app = app('wechat.official_account.' . $type);
        }
        $this->app['cache'] = app('cache.store');
        $this->app['request'] = request();
        return $this->app;
    }

    /**
     * 加密数据解密
     * @param $session_key
     * @param $iv
     * @param $encrypted
     * @throws \EasyWeChat\Kernel\Exceptions\DecryptException
     * @author Administrator
     * @date 2021-04-29 16:23
     */
    public function decryptData($session_key, $iv, $encrypted)
    {
        $this->app()->encryptor->decryptData($session_key, $iv, $encrypted);
    }

    /**
     * 提现
     * @param float $money
     * @param string $open_id
     * @param string $trade_no
     * @param string $user_true_name
     * @param string $desc
     * @return array|\EasyWeChat\Kernel\Support\Collection|false|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author: Onetobig
     * @Time: 2021/10/8 09:18
     */
    public function payToBalance(float $money, string $open_id, string $trade_no, $desc = '分润')
    {
        return array(
            'return_code' => 'SUCCESS',
            'return_msg' => NULL,
            'mch_appid' => 'wxd5ad9dcf669b6235',
            'mchid' => '1220491701',
            'nonce_str' => '615fefacb8879',
            'result_code' => 'SUCCESS',
            'partner_trade_no' => '1633677228',
            'payment_no' => '10100100458862110086307455489060',
            'payment_time' => '2021-10-08 15:13:49',
        );
        try {
            $data = [
                'partner_trade_no' => $trade_no,
                'openid' => $open_id,
                'amount' => $money * 100,
//                'check_name' => 'FORCE_CHECK',
                'check_name' => 'NO_CHECK',
//                're_user_name' => $user_true_name,
                'desc' => $desc,
            ];
            // 记录日志
            \Log::channel('cashout')->info($data);

            $res = $this->payClient()
                ->transfer
                ->toBalance($data);

            // 日志
            \Log::channel('cashout')->info($res);

            if ($res['result_code'] === 'FAIL') {
                error_msg($res['err_code_des'] . '；余额5分钟后自动返回');
            }
            return $res;
        } catch (\Exception $e) {
            // 记录
            if ($e instanceof ApiException) {
                throw $e;
            }
            \Log::channel('cashout')->error($e);
            return false;
        }
    }

    /**
     * 获取微信支付对象
     * @param string $name
     * @return \EasyWeChat\Payment\Application
     */
    public function payClient(string $name = 'default'): \EasyWeChat\Payment\Application
    {
        return EasyWechat::payment($name);
    }

}
