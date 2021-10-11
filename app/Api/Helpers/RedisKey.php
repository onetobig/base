<?php


namespace App\Api\Helpers;


class RedisKey
{
    /**
     * 盲盒-不重复的组编号 set
     */
    const PRODUCT_ITEM_GROUP_NO_KEY = 'product:items:group_no';
    /**
     * 盲盒-不重复的编号 set
     */
    const PRODUCT_ITEM_NO_KEY = 'product:items:no';
    /**
     * 盲盒-包装盒归属集合 sorted set
     */
    const PRODUCT_ITEM_BOX_KEY_PREFIX = 'product:items:box:';

    /**
     * 盲盒-选盒中  hash map
     */
    const PRODUCT_ITEM_BOX_SELECTING_PREFIX = 'product:items:box:selecting:';

    /**
     * 点赞
     */
    const LIKES_TABLE = 'likes_table';

    /**
     * 关注
     */
    const FOLLOW_TABLE = 'follow_table';

    /**
     * 热度排行榜
     */
    const POPULAR_RANK_TABLE = 'popular_rank_table';
}
