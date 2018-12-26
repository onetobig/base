<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Teachers extends Model
{
    protected $fillable = ['name', 'avatar', 'introduce'];

    public function getAvatarUrlAttribute()
    {
        if (Str::startsWith($this->attributes['avatar'], ['http://', 'https://'])) {
            return $this->attributes['avatar'];
        }
        return \Storage::disk('admin')->url($this->attributes['avatar']);
    }
}
