<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('id');
            $table->string('name')->default('')->comment('用户名');
            $table->string('nickname')->nullable()->default('')->comment('昵称');
            $table->string('openid')->nullable()->default('')->comment('小程序 openid')->index();
            $table->string('union_id')->nullable()->index()->comment('微信 unionid');
            $table->string('no')->nullable()->unique()->comment('用户唯一标识，可用作推荐码');
            $table->string('phone')->nullable()->index()->comment('手机号码');
            $table->string('avatar')->nullable()->comment('头像地址');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
        \DB::statement("ALTER TABLE users COMMENT='用户表'");//表注释
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
