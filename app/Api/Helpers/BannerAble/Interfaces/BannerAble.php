<?php


namespace App\Api\Helpers\BannerAble\Interfaces;

use App\Models\Banner;
use App\Models\Institution;

/**
 * @param string $bannerAble
 * Interface BannerAble
 * @package App\Api\Helpers\BannerAble\Interfaces
 */

interface BannerAble
{
    public function getBannerMediaUrlAttribute();

    public function getBannerMediaTypeAttribute();

    public function getBannerPathAttribute();

    public function getBannerTypeAttribute();

    public function seedToBanner();

    public function unSeedFromBanner();

    public function getShowInBannerColumnName();

    public function bannerDeleted(Banner $banner);

    public function getBannerShow();
}
