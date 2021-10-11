<?php

use \Illuminate\Support\Facades\Redis;

if (!function_exists('route_class')) {
    function route_class()
    {
        return str_replace('.', '-', Route::currentRouteName());
    }
}

if (!function_exists('parse_xml')) {
    function parse_xml($xml)
    {
        // 用 simplexml_load_string 函数初步解析 XML，返回值为对象，再通过 normalize_xml 函数将对象转成数组
        return normalize_xml(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_COMPACT | LIBXML_NOCDATA | LIBXML_NOBLANKS));
    }
}

if (!function_exists('normalize_xml')) {
    // 将 XML 解析之后的对象转成数组
    function normalize_xml($obj)
    {
        $result = null;
        if (is_object($obj)) {
            $obj = (array)$obj;
        }
        if (is_array($obj)) {
            foreach ($obj as $key => $value) {
                $res = normalize_xml($value);
                if (('@attributes' === $key) && ($key)) {
                    $result = $res;
                } else {
                    $result[$key] = $res;
                }
            }
        } else {
            $result = $obj;
        }
        return $result;
    }
}

if (!function_exists('get_str_format')) {
    function get_str_format($str)
    {
        $strA = trim($str);
        $lenA = strlen($strA); //检测字符串实际长度
        $lenB = mb_strlen($strA, 'utf-8'); //文件的编码方式要是UTF8
        if ($lenA === $lenB) {
            return '1'; //全英文
        } else {
            if (0 == $lenA % $lenB) {
                return '2'; //全中文
            } else {
                return '3'; //中英混合
            }
        }
    }
}

if (!function_exists('get_client_ip')) {
    function get_client_ip()
    {
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            // for php-cli(phpunit etc.)
            $ip = defined('PHPUNIT_RUNNING') ? '127.0.0.1' : gethostbyname(gethostname());
        }

        return filter_var($ip, FILTER_VALIDATE_IP) ?: '127.0.0.1';
    }
}

if (!function_exists('error_msg')) {
    function error_msg($msg = '操作失败', $code = 400)
    {
        throw new \App\Exceptions\ApiException($msg, $code);
    }
}

if (!function_exists('error_if')) {
    function error_if($condition, $msg = '操作失败', $code = 400)
    {
        if ($condition) {
            throw new \App\Exceptions\ApiException($msg, $code);
        }
    }
}

if (!function_exists('site_option')) {
    function site_option($key = null, $default = null)
    {
        $cache_key = 'site_option';
        if (is_null($key)) {
            return null;
        }
        $redis = app('redis');
        if (is_array($key)) {
            foreach ($key as $k => $value) {
                if ($value === null) {
                    $redis->hDel($cache_key, $k);
                    continue;
                }
                $value = is_numeric($value) && !in_array($value, [INF, -INF]) && !is_nan($value) ? $value : serialize($value);
                $redis->hSet($cache_key, $k, $value);
            }
            return true;
        }
        if (!$redis->HEXISTS($cache_key, $key)) {
            return $default;
        }
        $value = $redis->HGET($cache_key, $key);
        return is_numeric($value) ? $value : unserialize($value);
    }
}


if (!function_exists('redis_lock')) {
    function redis_lock($key, $ttl, $value)
    {
        $r = Redis::setNx($key, $value);
        if ($r) {
            Redis::expire($key, $ttl);
        }

    }
}

if (!function_exists('get_format_money')) {
    /**
     * @note 获取value的数值
     * 默认value
     */
    function get_format_money($money)
    {
        $int_res = (int)$money;
        if ($int_res < $money) {
            return (float)$money;
        }
        return $int_res;
    }
}


if (!function_exists('get_format_date')) {
    /**
     * @note 获取value的数值
     * 默认value
     */
    function get_format_date($date)
    {
        return \Carbon\Carbon::createFromTimestamp(strtotime($date));
    }
}

if (!function_exists('database_remark')) {
    /**
     * @note 获取value的数值
     * 默认value
     */
    function database_remark(array $data)
    {
        $res = [];
        foreach ($data as $k => $v) {
            $res[] = $k . '=' . $v;
        }
        return join('，', $res);
    }
}

if (!function_exists('get_filter_params')) {
    /**
     * @note 获取value的数值
     * 默认value
     */
    function get_filter_params(array $data)
    {
        $res = [];
        foreach ($data as $k => $v) {
            $res[] = [
                'key' => $k,
                'text' => $v
            ];
        }
        return $res;
    }
}

if (!function_exists('settings')) {

    function settings($key = null, $default = null)
    {
        if ($key === null) {
            return app(App\Api\Helpers\Settings::class);
        }
        return app(App\Api\Helpers\Settings::class)->get($key, $default);
    }

}

if (!function_exists('make_excerpt')) {
    function make_excerpt($value, $length = 200)
    {
        $excerpt = trim(preg_replace('/\r\n|\r|\n+/', '', strip_tags($value)));

        return str_limit($excerpt, $length, '');
    }
}

if (!function_exists('backend_routes')) {
    function backend_routes($name, $controller)
    {
        // 自定义专区
        Route::post("{$name}/index", [$controller, 'index'])->name("backend.{$name}.index");
        Route::post("{$name}/destroy", [$controller, 'destroy'])->name("backend.{$name}.destroy");
        Route::post("{$name}/enable", [$controller, 'enable'])->name("backend.{$name}.enable");
        Route::post("{$name}/disable", [$controller, 'disable'])->name("backend.{$name}.disable");
        Route::post("{$name}/store", [$controller, 'store'])->name("backend.{$name}.store");
        Route::post("{$name}/update", [$controller, 'update'])->name("backend.{$name}.update");
        Route::post("{$name}/edit", [$controller, 'edit'])->name("backend.{$name}.edit");
    }
}

