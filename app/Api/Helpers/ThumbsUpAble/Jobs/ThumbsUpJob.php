<?php

namespace App\Api\Helpers\ThumbsUpAble\Jobs;

use App\Api\Helpers\ThumbsUpAble\Interfaces\ThumbsUpAble;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Redis;
use App\Models\ThumbsUp as ThumbsUpModel;

class ThumbsUpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $model;
    protected $user_id;

    public function __construct(ThumbsUpAble $model, $user_id)
    {
        $this->model = $model;
        $this->user_id = $user_id;
    }

    public function handle()
    {
        $liked = Redis::sismember($this->model->getThumbsUpKey(), $this->user_id);
        $item = ThumbsUpModel::withTrashed()
            ->firstOrCreate([
                'user_id' => $this->user_id,
                'model' => get_class($this->model),
                'model_id' => $this->model->id,
            ]);
        if ($liked) {
            $item->restore();
        } else {
            $item->delete();
        }


        $this->model->syncThumbsUpCountToDatabase();
    }
}
