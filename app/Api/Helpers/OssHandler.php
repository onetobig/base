<?php
/**
 * Created by PhpStorm.
 * User: onetobig (LiaoWeiQiang)
 * Date: 2019/3/12
 * Time: 15:54
 */

namespace App\Api\Helpers;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Sts\Sts;
use App\Exceptions\ApiException;
use DateTime;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Image;
use OSS\Core\OssException;
use OSS\Http\RequestCore_Exception;
use Route;

class OssHandler
{
    const OSS_IMAGE_MAP_KEY = 'oss_webp_image_maps';
    const OSS_ORI_IMAGE_MAP_KEY = 'oss_ori_image_maps';
    const OSS_VIDEO_POSTER = 'oss_video_poster';

    protected $id;
    protected $key;
    protected $host;

    public function __construct()
    {
        $this->id = config('filesystems.disks.oss.access_key');
        $this->key = config('filesystems.disks.oss.secret_key');
        if (config('filesystems.disks.oss.isCName')) {
            $this->host = config('filesystems.disks.oss.endpoint');
        } else {
            $this->host = 'https://' . config('filesystems.disks.oss.bucket') . '.'
                . config('filesystems.disks.oss.endpoint');
        }
    }

    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $dir 图片目录
     * @param int $size 大小，单位：MB
     * @param int $expire 过期时间
     * @return array
     * @throws Exception
     */
    public function getPolicyForMiniAppJs($dir, $size = 2, $expire = 600)
    {
        $now = time();
        $end = $now + $expire;
        $expiration = self::gmt_iso8601($end);
        // MB
        $size = $size * 1024 * 1024;

        //最大文件大小.用户可以自己设置
        $condition = array(0 => 'content-length-range', 1 => 0, 2 => $size);
        $conditions[] = $condition;

        //表示用户上传的数据,必须是以$dir开始, 不然上传会失败,这一步不是必须项,只是为了安全起见,防止用户通过policy上传到别人的目录
        $dir = $dir . session_create_id();
        $start = array(0 => 'starts-with', 1 => '$key', 2 => $dir);
        $conditions[] = $start;

        $arr = array('expiration' => $expiration, 'conditions' => $conditions);
        //echo json_encode($arr);
        //return;
        $policy = json_encode($arr);
        $base64_policy = base64_encode($policy);
        $string_to_sign = $base64_policy;
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $this->key, true));
        $callback_url = route('oss.notify');
        $callback_param = array('callbackUrl' => $callback_url,
            'callbackBody' => 'bucket=${bucket}&filename=${object}&size=${size}&mimeType=${mimeType}',
            'callbackBodyType' => "application/x-www-form-urlencoded");
        $callback_string = json_encode($callback_param);
        $base64_callback_body = base64_encode($callback_string);

        $response = array();
        $response['accessid'] = $this->id;
        $response['host'] = $this->host;
        $response['upload_host'] = config('filesystems.disks.oss.cdn');
        $response['policy'] = $base64_policy;
        $response['signature'] = $signature;
        $response['expire'] = $end;
        $response['callback'] = $base64_callback_body;
        //这个参数是设置用户上传指定的前缀
        $response['dir'] = $dir;
        return $response;
    }

    /**
     * @param string $dir 图片目录
     * @param int $size 大小，单位：MB
     * @param int $expire 过期时间
     * @return array
     * @throws Exception
     */
    public function getPolicyForJs($dir, $size = 2, $expire = 600)
    {
        // 检查容量
        AlibabaCloud::accessKeyClient(
            config('filesystems.disks.oss.access_key'),
            config('filesystems.disks.oss.secret_key')
        )->name('sts');

        $date = now()->format('Ymd');

        $dir = rtrim(ltrim($dir, '\\/'), '\\/');
        $dir = "/{$dir}/";
        $bucket = config('filesystems.disks.oss.bucket');
        $policy = '{"Statement":[{"Action":["oss:PutObject"],"Effect":"Allow","Resource":["acs:oss:*:*:' . $bucket .
            $dir . '*"]}],"Version":"1"}';
        $res = Sts::v20150401()
            ->assumeRole()
            ->client('sts')
            ->regionId('cn-hangzhou')
            ->withRoleArn(config('filesystems.disks.oss.role_arn'))
            ->withRoleSessionName(session_create_id())
            ->withDurationSeconds(1200)
            ->withPolicy($policy)
            ->request();
        $credentials = $res->toArray()['Credentials'];

        $data = array_merge($credentials, [
            'Endpoint' => config('filesystems.disks.oss.endpoint'),
            'Bucket' => $bucket,
            'Host' => config('filesystems.disks.oss.cdn'),
            'Filename' => $dir . session_create_id(),
        ]);

        return $data;
    }

    public function gmt_iso8601($time)
    {
        $dtStr = date("c", $time);
        $mydatetime = new DateTime($dtStr);
        $expiration = $mydatetime->format(DateTime::ISO8601);
        $pos = strpos($expiration, '+');
        $expiration = substr($expiration, 0, $pos);
        return $expiration . "Z";
    }

    public function getOssVideoPoster($url, $ms = 1000, $width = 0, $height = 0)
    {
        $cdn = config('filesystems.disks.oss.cdn');
        if (!Str::startsWith($url, $cdn)) {
            return '';
        }

        // 截屏参数
        $param_str = "video/snapshot,t_{$ms},f_png,m_fast";
        if ($width) {
            $param_str .= ",w_{$width}";
        }
        if ($height) {
            $param_str .= ",w_{$height}";
        }

        // 解析
        $path = parse_url($url)['path'];
        $basename = basename($path);
        $basename = substr($basename, 0, strpos($basename, '.'));
        $filename = dirname($path) . '/' . $basename . '.png';
        $filename = ltrim($filename, '/\\');
        $path = ltrim($path, '/\\');

        // 查缓存
        if ($url = Redis::hget(self::OSS_VIDEO_POSTER, $path)) {
            return rtrim($cdn, '\\/') . '/' . $url;
        }

        // 处理截帧并保存
        $res = app(OssClient::class)->saveAs(
            config('filesystems.disks.oss.bucket'),
            $path,
            $filename,
            $param_str
        );
        if (($res['status'] ?? '') === 'OK') {
            Redis::hset(OssHandler::OSS_VIDEO_POSTER, $path, $filename);
        }

        // 返回截帧路径
        return rtrim($cdn, '\\/') . '/' . $filename;
    }

    public function record($model, $model_id, $file_url)
    {
        dispatch(new Record($model, $model_id, $file_url));
    }

    /**
     * 图片压缩
     * @param $url
     * @return mixed
     * @throws OssException
     * @throws RequestCore_Exception
     */
    public function zipWebp($url)
    {
        // 解析
        if ($pare_url = parse_url($url)) {
            $path = $pare_url['path'];
        } else {
            $path = $url;
        }
        // 从缓存取压缩数据
        $zip_url = Redis::hget(self::OSS_IMAGE_MAP_KEY, $path);
        $path = $zip_url === false ? $path : $zip_url;

        $cdn = config('filesystems.disks.oss.cdn');

        // webp 不再处理
        if (Str::endsWith($path, ['.webp'])) {
            return rtrim($cdn, '\\/') . '/' . ltrim($path, '\\/');
        }

        // 剔除最左边的 / \
        $path = ltrim($path, '\\/');
        // 目录名
        $dir = dirname($path);
        // 文件名
        $basename = basename($path);
        $name = substr($basename, 0, strpos($basename, '.'));

        // 压缩文件名
        if (!in_array($dir, ['.', './', '/', '\\', '.\\']) && $dir) {
            $filename = $dir . '/' . $name . '.webp';
        } else {
            $filename = $name . '.webp';
        }

        // 上传
        $res = app(OssClient::class)->saveAs(
            config('filesystems.disks.oss.bucket'),
            $path,
            $filename,
            'image/format,webp/resize,w_1024'
        );
        $url = $path;
        if (($res['status'] ?? '') === 'OK') {
            Redis::hset(OssHandler::OSS_IMAGE_MAP_KEY, $path, $filename);
            Redis::hset(OssHandler::OSS_ORI_IMAGE_MAP_KEY, $filename, $path);
            $url = $filename;
        }
        return rtrim($cdn, '\\/') . '/' . $url;
    }

    public function copy($from, $to)
    {
        // 上传
        $res = app(OssClient::class)->copyObject(
            config('filesystems.disks.oss.bucket'),
            ltrim($from, '/'),
            config('filesystems.disks.oss.bucket'),
            ltrim($to, '/')
        );
        return $res;
    }

    public function putSymlink($from, $to)
    {
        //return $this->copy($from, $to);
        // 上传
        $res = app(OssClient::class)->putSymlink(
            config('filesystems.disks.oss.bucket'),
            ltrim($to, '/'),
            ltrim($from, '/')
        );
        return $res;
    }

    public function getDownloadUrl($path, $filename, $timeout = 86400)
    {
        $path = parse_url($path)['path'] ?? error_msg('文件路径错误');
        $sub_resource = "response-content-disposition=attachment;filename={$filename}";
        $url = app(OssClient::class)->signUrl(
            config('filesystems.disks.oss.bucket'),
            ltrim($path, '/') . '?' . $sub_resource,
            $timeout
        );
        $path_info = parse_url($url);
        $res = config('filesystems.disks.oss.cdn') . '/' . ltrim($path, '/') . '?' . $sub_resource . '&' .
            $path_info['query'];
        return $res;
    }

    /**
     * @author onetobig
     * @date 2020-11-25 10:12
     */
    public function uploadFile($filename, $path)
    {
        if (config('app.is_aliyun')) {
            $filesystem = Storage::disk('internal_oss');
        } else {
            $filesystem = Storage::disk('oss');
        }
        if ($path instanceof Response) {
            $content = $path->getContent();
        } else {
            $content = file_get_contents($path);
        }
        try {
            $res = $filesystem->put(
                $filename,
                $content
            );
            if (!$res) {
                error_msg('文件上传失败');
            }
        } catch (Exception $e) {
            error_msg('文件上传失败');
        }
        $url = $this->getOssFileUrl($filename);
        return $url;
    }

    /**
     * 上传文件到 oss
     * @param $filename
     * @param $image
     * @return mixed|string
     * @throws ApiException
     */
    public function uploadImageToOss($filename, $image)
    {
        if (!$image instanceof Image) {
            $image = Image::make($image);
        }
        if (config('app.is_aliyun')) {
            $filesystem = Storage::disk('internal_oss');
        } else {
            $filesystem = Storage::disk('oss');
        }
        try {
            $res = $filesystem->put(
                $filename,
                $image->stream('png')->getContents()
            );
            if (!$res) {
                error_msg('图片上传失败');
            }
        } catch (Exception $e) {
            error_msg('图片上传失败');
        }
        $url = $this->getOssFileUrl($filename);
        if ($cdn = config('filesystems.disks.oss.cdn')) {
            return rtrim($cdn, '/\\') . parse_url($url)['path'];
        }
        return $url;
    }

    /**
     * 获取 oss 文件路径
     * @param $url
     * @return mixed|string
     */
    public function getOssFileUrl($url)
    {
        if (!$url) {
            return '';
        }
        if (!Str::startsWith($url, ['http://', 'https://'])) {
            $url = Storage::disk('oss')->getUrl($url);
        }
        return $url;
    }

    public function getOriPath($path)
    {
        // 解析
        if (!$pare_url = parse_url($path)) {
            return $path;
        }
        $zip_path = $pare_url['path'];
        $cdn = config('filesystems.disks.oss.cdn');
        $cdn = rtrim($cdn, '/\\');
        $ori_path = Redis::hget(OssHandler::OSS_ORI_IMAGE_MAP_KEY, $zip_path);
        if ($ori_path) {
            return $cdn . '/' . $ori_path;
        }
        return $path;
    }
}
