<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisitsTable extends Migration
{
    public function up()
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->morphs('visitable');
            $table->dateTime('last_visit_at')->nullable()->comment('上次浏览时间');
            $table->timestamps();
        });
        DB::statement("ALTER TABLE visits COMMENT='浏览记录表'");//表注释
    }

    public function down()
    {
        Schema::dropIfExists('visits');
    }
}
