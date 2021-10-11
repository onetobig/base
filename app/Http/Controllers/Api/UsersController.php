<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\Api\UserResource;
use App\Models\User;
use App\Models\UserBalance;
use App\Services\MiniProgramService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Passport\Passport;

class UsersController extends ApiController
{
    /**
     * @var User
     */
    protected User $mode;

    public function __construct()
    {
        $this->mode = new User();
    }

    public function responseWithToken(User $user)
    {
        Passport::personalAccessTokensExpireIn(today()->addMonth());
        return $this->success([
            'need_phone' => false,
            'access_token' => $user->createToken('api')->accessToken,
            'user' => new \App\Http\Resources\Api\UserResource($user),
            'was_recently_created' => $user->wasRecentlyCreated,
        ]);
    }


    public function loginByWeixin(Request $request, MiniProgramService $service)
    {
        $this->validate($request, [
            'code' => 'required|string',
            'avatar' => 'required|string',
            'nickname' => 'required|string',
        ]);
        $code = $request->input('code', '');
        $mini_app = $service->app();
        $user_data = $mini_app->auth->session($code);
        if (isset($user_data['errcode'])) {
            return $this->failed('code 不正确');
        }
        $openid = $user_data['openid'];
        $user_data = array_merge($user_data, $request->only([
            'avatar',
            'nickname',
        ]));
        $user = User::query()
            ->where('openid', $openid)
            ->first();

        if (!$user) {
            $user = User::create([
                'nickname' => $user_data['nickname'],
                'name' => $user_data['nickname'],
                'avatar' => $user_data['avatar'],
                'openid' => $openid,
                'vip_type' => User::VIP_TYPE_NEW,
            ]);
        }

        // 更新昵称和头像
        $user->update([
            'nickname' => $user_data['nickname'],
            'avatar' => $user_data['avatar'],
        ]);
        return $this->responseWithToken($user);
    }

    public function logout(Request $request)
    {
        $request->user()->logout();
        return $this->success();
    }


    public function update(Request $request)
    {
        $this->validate($request, [
            'avatar' => ['url'],
            'name' => 'string|max:50',
            'gender' => [Rule::in(array_keys(User::$genderMap))],
            'signing' => 'string|max:255'
        ]);
        $res = app(MiniProgramService::class)
            ->checkText($request->input('avatar') . $request->input('signing'));
        if (!$res) {
            error_msg('文本内容含有敏感信息，请修改');
        }

        $user = $request->user();
        $user->fill($request->only([
            'avatar',
            'name',
            'signing',
            'gender',
        ]));
        $user->save();

        return $this->me($request);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        return $this->success(new UserResource($user));
    }

    public function scholarship(Request $request)
    {
        $user = $request->user()->refresh();
        $records = UserBalance::query()
            ->where('user_id', $user->id)
            ->paginate($request->input('page_size', 15));
        $res = $this->paginate($records);
        $res['meta']['scholarship'] = get_format_money($user->scholarship);
        $res['meta']['money'] = get_format_money($user->money);
        return $this->res($res);
    }
}
