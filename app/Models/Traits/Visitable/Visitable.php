<?php

namespace App\Models\Traits\Visitable;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait Visitable
{
    /**
     * Return followers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function visitors(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'visitors',
            'visitable_id',
            'user_id'
        )
            ->where('visitable_type', $this->getMorphClass());
    }
}
