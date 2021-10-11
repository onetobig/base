<?php

namespace App\Services;

class SettingService
{
    public function base($settings = [])
    {
        $init = [
            'init_base' => 3
        ];

        return $this->settings($init, $settings);
    }

    /**
     * 返回、更新设置
     * @param array $init 设置的初始值
     * @param array $settings 设置更新值
     * @return array 设置数组
     * @author: Onetobig
     * @Time: 2021/10/11 10:51
     */
    protected function settings(array $init, array $update_settings = [])
    {
        // 从设置值取，没有设置给默认值
        $sys_settings = settings()->all();
        foreach ($init as $k => $v) {
            $init[$k] = array_key_exists($k, $sys_settings) ? $sys_settings[$k] : $v;
        }

        // 没有更新，直接返回
        if (!$update_settings) {
            return $init;
        }

        // 有更新，只取出对应的值进行更新
        $update_settings = array_only($update_settings, array_keys($init));
        settings()->put($update_settings);

        // 返回最新的设置值
        return array_merge($init, $update_settings);
    }
}
