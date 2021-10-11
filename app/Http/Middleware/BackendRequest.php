<?php

namespace App\Http\Middleware;


use App\Exceptions\ApiAuthorizationException;
use App\Models\BackendUser;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Closure;

class BackendRequest extends ApiRequest
{
    protected $authModel = BackendUser::class;
    protected $guard = 'backend';
    protected $tokenParam = 'backendToken';
}
