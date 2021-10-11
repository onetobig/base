<?php

namespace App\Api\Helpers;
use Laravel\Passport\HasApiTokens as BaseTrait;

trait HasApiTokens
{
    use BaseTrait;

    public function logout()
    {
        $token = $this->token();
        $token->delete();
        $key = \config('passport.cache.prefix') . $token->id;
        cache()->forget($key);
        return true;
    }

    public function getTokenNameAttribute()
    {
        $token = $this->token();
        if (!$token->name) {
            $token->refresh();
            $key = \config('passport.cache.prefix') . $token->id;
            cache()->forget($key);
        }
        return $token->name;
    }
}
