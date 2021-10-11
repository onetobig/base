<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/15
 * Time: 09:33
 */

namespace App\Api\Helpers;

use App\Exceptions\ApiException;
use App\Models\Institution;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as FoundationResponse;


trait ApiResponse
{
    /**
     * @var int
     */
    protected $statusCode = FoundationResponse::HTTP_OK;

    public function res(iterable $data, $status = 'success', $code = null)
    {
        return $this->status($status, $data, $code);
    }

    /**
     * @param $status
     * @param array $data
     * @param null $code
     * @return mixed
     */
    public function status($status, iterable $data, $code = null)
    {
        $status = $this->statusFormat($status);

        $data = array_merge($status, (array)$data);
        if (!isset($data['msg'])) {
            $data['msg'] = '操作成功';
        }
        $type = get_str_format($data['msg']);
        if ($type == 1) {
            $msgTime = 1.5;
        } else {
            $msgTime = round(mb_strlen($data['msg']) / 10 * 1.5, 1);
            $msgTime = max(1.5, $msgTime);
            $msgTime = min(3, $msgTime);
        }
        $msgTime = $msgTime * 1000;

        if (!isset($data['msgTime'])) {
            $data['msgTime'] = $msgTime;
        }

        $this->setStatusCode(200);
        return $this->respond($data);

    }

    public function statusFormat($status)
    {
        $res = [
            'status' => $status,
            'code' => $this->statusCode,
        ];
        return $res;
    }

    /**
     * @param $data
     * @param array $header
     * @return mixed
     */
    public function respond($data, $header = [])
    {
        // 数据小数点问题
        ini_set('serialize_precision', ini_get('precision'));
        $response = Response::json($data, $this->getStatusCode(), $header)
            ->setEncodingOptions(JSON_UNESCAPED_UNICODE);

        $request = optional(request());
        $name = optional($request->route())->getName();
        $api_version = '';
        $switch = 1;

        if (app()->environment() === 'local') {
            $response->headers->set('user_id', optional($request->user())->id);
        }
        $response->headers->set('Api_version', $api_version ?? '1.0.0');
        $response->headers->set('Api_switch', $switch);
        return $response;
    }

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode)
    {

        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function internalError($message = "Internal Error!")
    {

        return $this->failed($message, FoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param $message
     * @param int $code
     * @param string $status
     * @return mixed
     */
    public function failed($message, $code = FoundationResponse::HTTP_BAD_REQUEST, $status = 'error')
    {
        return $this->setStatusCode($code)->message($message, $status);
    }

    /**
     * @param $message
     * @param string $status
     * @return mixed
     */
    public function message($message, $status = "success")
    {

        return $this->status($status, [
            'msg' => $message
        ]);
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function
    created($message = "created")
    {
        return $this->setStatusCode(FoundationResponse::HTTP_CREATED)
            ->message($message);

    }

    /**
     * @param $data
     * @param string $status
     * @return mixed
     */
    public function success($data = [], $msg = '操作成功', $status = "success", $other = [])
    {
        $data = compact('data');
        if ($msg) {
            $data['msg'] = $msg;
        }
        $data = array_merge($data, $other);
        return $this->status($status, $data);
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function notFond($message = 'Not Fond!')
    {
        return $this->failed($message, Foundationresponse::HTTP_NOT_FOUND);
    }

    public function resource($object, $additional = [], $msg = '保存成功')
    {
        if ($msg) {
            $additional['msg'] = $msg;
        }
        $object->additional = array_merge_recursive($this->statusFormat('success'), $object->additional, $additional);
        $response = $object->response()
            ->setEncodingOptions(JSON_UNESCAPED_UNICODE);
        $data = json_decode($response->getContent(), true);
        return $this->respond($data);
    }
}
