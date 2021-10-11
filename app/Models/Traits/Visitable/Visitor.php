<?php
namespace App\Models\Traits\Visitable;

use App\Models\Visit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait Visitor
{
    /**
     * @param  \Illuminate\Database\Eloquent\Model  $object
     *
     * @return Visit
     */
    public function visit(Model $object): Visit
    {
        $attributes = [
            'visitable_type' => $object->getMorphClass(),
            'visitable_id' => $object->getKey(),
            'user_id' => $this->getKey(),
        ];

        /* @var \Illuminate\Database\Eloquent\Model $like */
        $visitor = \app(Visit::class);

        /* @var \Overtrue\LaravelLike\Traits\Likeable|\Illuminate\Database\Eloquent\Model $object */
        $model = $visitor->where($attributes)->firstOr(
            function () use ($visitor, $attributes) {
                $visitor->unguard();
                return $visitor->create($attributes);
            }
        );
        $model->update([
            'last_visit_at' => now(),
        ]);
        return $model;
    }

    public function visits()
    {
        return $this->hasMany(Visit::class);
    }
}
