<?php

namespace App\Providers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Carbon 中文化设置
        \Carbon\Carbon::setLocale('zh');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // 处理dingo的findOrFail 问题
        // 或许可以放在  ApiExceptionServiceProvider 这样的地方
        app('api.exception')->register(function (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            abort(404);
        });

        \API::error(function (\Illuminate\Auth\Access\AuthorizationException $exception) {
            abort(403, $exception->getMessage());
        });
        // 开启日志
        if (app()->environment('local')) {
            \DB::listen(function (QueryExecuted $query) {
                $sqlWithPlaceholders = str_replace(['%', '?'], ['%%', '%s'], $query->sql);
                $bindings = $query->connection->prepareBindings($query->bindings);
                $pdo = $query->connection->getPdo();
                \Log::info(vsprintf($sqlWithPlaceholders, array_map([$pdo, 'quote'], $bindings)));
            });
        }
    }
}
