<?php

namespace App\Http\Middleware;

use App\Api\Helpers\ExceptionReport;
use App\Exceptions\ApiAuthorizationException;
use App\Models\User;
use Closure;
use Fruitcake\Cors\HandleCors;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ApiRequest implements AuthenticatesRequests
{
    /**
     * The authentication factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param \Illuminate\Contracts\Auth\Factory $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    protected $authModel = User::class;
    protected $guard = 'api';
    protected $tokenParam = 'apiToken';

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next, $strict = null)
    {
        // 开始时间
        $start = microtime(true);
        // 添加参数
        $request = $this->setRequestParam($request);
        // 原始请求参数
        $input_data = $request->all();
        try {
            // 处理授权登录问题
            $this->auth($request, $strict, $this->guard);
        } catch (\Exception $e) {
            // 将方法拦截到自己的ExceptionReport
            $reporter = ExceptionReport::make($e);
            $error_res = $reporter->failed($e->getMessage(), 401);
            return $this->corsResponse($error_res);
        }
        // 处理
        try {
            $response = $next($request);
        } catch (\Exception $e) {
            $reporter = ExceptionReport::make($e);
            $error_res = $reporter->failed($e->getMessage(), $e->getCode());
            return $this->corsResponse($error_res);
        }
        return $this->corsResponse($response);
    }

    public function corsResponse($response)
    {
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Cookie, Accept');
        $response->header('Access-Control-Allow-Methods', '*');
        $response->header('Access-Control-Allow-Credentials', 'false');
        return $response;
    }

    /**
     * 标识 api 请求
     * @param Request $request
     * @return Request
     * @author Administrator
     * @date 2021-04-20 15:56
     */
    protected function setRequestParam(Request $request)
    {
        // 标识
        $request->server->set('HTTP_FROM', 'api');
        $request->server->set('REQUEST_TIME', time());
        $request->headers->set('Accept', 'application/json');

        // token
        if ($token = $request->input($this->tokenParam)) {
            $request->offsetUnset($this->tokenParam);
            if (!Str::startsWith($token, ['Bearer ', 'bearer '])) {
                $token = 'Bearer ' . $token;
            }
            $request->headers->set('Authorization', $token);
        }

        return $request;
    }

    /**
     * 处理授权问题
     * @param Request $request
     * @param Closure $next
     * @param $strict
     * @return mixed
     * @author Administrator
     * @date 2021-04-20 15:57
     */
    protected function auth(Request $request, $strict, $guard)
    {
        if ((int)$strict === -1) {
            return;
        }
        if ($strict || $request->bearerToken()) {
            try {
                $this->authenticate($request, (array)$guard);
                // 检查 token
                $this->checkToken($request);
            } catch (\Exception $e) {
                throw new ApiAuthorizationException('请重新登录', '请重新登录');
            }
        }
    }

    /**
     * 添加日志
     * @param float $start 请求开始的时间
     * @param Request $request
     * @param array $input_data 请求的参数
     * @author Administrator
     * @date 2021-04-20 15:57
     */
    protected function storeApiLog($start, Request $request, $input_data)
    {
        // 记录日志
//        $cost = microtime(true) - $start;
//        $cost *= 1000;

        /*
        if ($cost > 200) {
            Log::channel('slow')->info(
                sprintf(
                    "用户：%s 地址:%s 花费：%s ms\n数据：",
                    optional($request->user())->id,
                    $request->getRequestUri(),
                    $cost
                )
            );
        }
        Log::channel('runtime')->info(
            sprintf(
                "用户：%s 地址:%s 花费：%s ms \n数据：",
                optional($request->user())->id,
                $request->getRequestUri(),
                $cost
            )
        );
        Log::channel('runtime')->info($input_data);
        Log::channel('runtime')->info(get_client_ip() ?? '');
        */
    }

    /**
     * 检查是否为 api 的 token
     * @param Request $request
     * @throws AuthorizationException
     * @author Administrator
     * @date 2021-04-21 18:12
     */
    protected function checkToken(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return;
        }
        if (!$user instanceof $this->authModel) {
            throw new AuthorizationException('请重新登录');
        }
        // 检查名字
        if ($user->token_name !== $this->guard) {
            throw new AuthorizationException('请重新登录');
        }
    }

    /**
     * Determine if the user is logged in to any of the given guards.
     *
     * @param \Illuminate\Http\Request $request
     * @param array $guards
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function authenticate($request, array $guards)
    {
        if (empty($guards)) {
            $guards = [null];
        }

        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {
                return $this->auth->shouldUse($guard);
            }
        }

        $this->unauthenticated($request, $guards);
    }

    /**
     * Handle an unauthenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @param array $guards
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function unauthenticated($request, array $guards)
    {
        throw new AuthenticationException(
            'Unauthenticated.', $guards, $this->redirectTo($request)
        );
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param \Illuminate\Http\Request $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        //
    }

    protected function dealToken(Request $request)
    {

    }

    protected function resolveRequestSignature($request)
    {
        if ($user = $request->user()) {
            return sha1($user->getAuthIdentifier());
        } elseif ($route = $request->route()) {
            return sha1($route->getDomain() . '|' . $request->ip());
        }

        error_msg('Unable to generate the request signature. Route unavailable.');
    }

    protected function computePv(Request $request)
    {
        return $request;
    }

}
