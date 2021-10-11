<?php


namespace App\Api\Helpers\BannerAble\Traits;


use App\Api\Helpers\BannerAble\Interfaces\BannerAble;
use App\Exceptions\ApiException;
use App\Models\Banner;
use App\Models\Institution;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Spatie\EloquentSortable\Sortable;

trait BannerAbleTrait
{

    /**
     * 显示 banner 的开关字段
     * @var array
     */
//    protected $bannerAble = [
//        'show_banner_column' => 'show_in_banner',
//    ];

    /**
     * 获取控制显示的字段名
     * @return string
     */
    public function getShowInBannerColumnName()
    {
        if (isset($this->bannerAble['show_banner_column']) && ! empty($this->bannerAble['show_banner_column'])) {
            return $this->bannerAble['show_banner_column'];
        }
        return 'show_in_banner';
    }

    public static function bootBannerAbleTrait()
    {
        // 添加 banne
        static::saved(function (BannerAble $model) {
            //banner 字段
            $banner_column = $model->getShowInBannerColumnName();

            // 有更新才进行操作
            $banner_show = $model->getBannerShow();
            $show_in_banner = $model->getAttribute($banner_column);
            $open = $banner_show && $show_in_banner;
            if ($open) {
                $model->seedToBanner();
            } else {
                $model->unSeedFromBanner();
            }

        });

        static::deleted(function (BannerAble $model) {
            $model->unSeedFromBanner();
        });
    }

    public function seedToBanner()
    {
        $banner =Banner::withTrashed()->firstOrNew([
            'model_id' => $this->id,
            'type' => $this->banner_type,
        ]);

        $banner->fill([
            'manager_id' => $this->manger_id ?? 0,
            'media_url' => $this->banner_media_url,
            'media_type' => $this->banner_media_type,
            'type' => $this->banner_type,
            'model_id' => $this->id,
            'path' => $this->banner_path,
            'content' => [],
            'deleted_at' => null,
            'show' => true,
        ]);
        $banner->save();
    }

    public function unSeedFromBanner()
    {
        $key = redis_key('model_banner', ['model' => $this->getTable()]);
        Redis::setBit($key, $this->id, 0);
        $banner = Banner::withTrashed()->where('type', $this->banner_type)
            ->where('model_id', $this->id)
            ->first();
        if ($banner) {
            $banner->delete();
        }
    }


    public function bannerDeleted(Banner $banner)
    {
        $key = redis_key('model_banner', ['model' => $this->getTable()]);
        Redis::setBit($key, $banner->model_id, 0);
        $column = $this->getShowInBannerColumnName();
        if ($column) {
            DB::table(static::getTable())
                ->where($this->getKeyName(), $banner->model_id)
                ->update([
                    $column => false,
                ]);
        }
    }
}
