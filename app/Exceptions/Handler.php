<?php

namespace App\Exceptions;

use App\Api\Helpers\ErrorMessages;
use App\Api\Helpers\ExceptionReport;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Exceptions\OAuthServerException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Database\QueryException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ErrorMessages;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        ApiAuthorizationException::class,
        AuthorizationException::class,
        AuthenticationException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        ApiException::class,
        OAuthServerException::class,
        \League\OAuth2\Server\Exception\OAuthServerException::class,
        InvalidRequestException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param \Exception $exception
     * @return void
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $e)
    {
        // 将方法拦截到自己的ExceptionReport
        $reporter = ExceptionReport::make($e);

        // 控制 api 错误返回
        if ($request->server->get('HTTP_FROM', '') === 'api') {
            $name = get_class($e);
            switch ($name) {
                case ValidationException::class:
                    // 全英文记录
                    $msg = $this->formatValidationMsg($e->validator);
                    return $reporter->failed($msg);
                case AuthenticationException::class:
                    return $reporter->failed('无权操作');
                case ModelNotFoundException::class:
                    return $reporter->failed($this->notFoundMessages($e->getModel()));
                case ApiAuthorizationException::class:
                    return $reporter->failed($e->getMessage(), 401);
                case ThrottleRequestsException::class:
                    return $reporter->failed($this->throttleMessage($request->route()->getName()), $e->getStatusCode());
                case QueryException::class:
                    \Log::channel('daily')->info($e);
                    if (app()->environment('local')) {
                        throw $e;
                    }
                    return $reporter->failed('内部错误，请联系工作人员处理', 500);
                case HttpException::class:
                    return $reporter->failed($e->getMessage(), $e->getStatusCode());
                case AuthorizationException::class:
                case OAuthServerException::class:
                case \League\OAuth2\Server\Exception\OAuthServerException::class:
                    return $reporter->failed('登录令牌已失效，请重新登录', 401);
                default:
                    return $reporter->failed($e->getMessage(), $e->getCode());
            }
        }

        if ($reporter->shouldReturn()) {
            return $reporter->report();
        }

        return parent::render($request, $e);
    }

    // 识别数组下标，替换 :index
    protected function formatValidationMsg($validator)
    {
        $msgs = $validator->errors()->toArray();
        foreach ($msgs as $key => $value) {
            preg_match('/.*\.(\d+)/', $key, $m);
            $index = $m[1] ?? '';
            if ($index === '') {
                preg_match('/.*\.(\d+)\..*/', $key, $m);
                $index = $m[1] ?? '';
            }

            $msg = $value[0];
            if ($index === '') {
                return $msg;
            }
            $index += 1;
            return str_replace(':index', $index, $msg);
        }
    }
}
