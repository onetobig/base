<?php


namespace App\Api\Helpers\VirtualJoinAble\Traits;


use App\Jobs\VirtualUser\Seed;
use App\Models\User;
use App\Models\VirtualActivityUser;
use Carbon\Carbon;
use Faker\Generator;

trait VirtualJoinAbleTrait
{

    public function seedVirtualUsers($count, Carbon $start_at, Carbon $end_at, $member_count = 3)
    {
        dispatch(new Seed($this, $count, $start_at, $end_at, $member_count));
    }

    public function seedVirtualUsersHandle($count, $start_at, $end_at, $member_count)
    {
        $member_count = min($member_count, 1);
        $count = min($count, 200);
        // 恢复已删除的记录
        VirtualActivityUser::withTrashed()
            ->where('model', $this->getTable())
            ->where('model_id', $this->getKey())
            ->update([
                'deleted_at' => null,
            ]);

        $seeded_count = VirtualActivityUser::query()
            ->where('model', $this->getTable())
            ->where('model_id', $this->getKey())
            ->count();

        $group_id = VirtualActivityUser::query()
            ->where('model', $this->getTable())
            ->where('model_id', $this->getKey())
            ->max('group_id');

        $faker = app(Generator::class);

        // 判断时间是否超出范围
        VirtualActivityUser::query()
            ->where('model', $this->getTable())
            ->where('model_id', $this->getKey())
            ->chunkById(100, function ($virtual_users) use ($start_at, $faker, $end_at) {
                foreach ($virtual_users as $virtual_user) {
                    if ($virtual_user->join_at < $start_at || $virtual_user->join_at > $end_at) {
                        $virtual_user->join_at = $faker->dateTimeBetween($start_at, $end_at);
                        $virtual_user->save();
                    }
                }
            });
        // 删除多出的记录
        if ($seeded_count >= $count) {
            $max_user = VirtualActivityUser::query()
                ->where('model', $this->getTable())
                ->where('model_id', $this->getKey())
                ->orderBy('id')
                ->offset($count)
                ->first();
            if ($max_user) {
                VirtualActivityUser::query()
                    ->where('model', $this->getTable())
                    ->where('model_id', $this->getKey())
                    ->where('id', '>=', $max_user->id)
                    ->update([
                        'deleted_at' => now(),
                    ]);
            }
            return true;
        }

        $seed_count = $count - $seeded_count;

        $users = User::query()->select('id')->where('user_type', User::USER_TYPE_ROBOT)->get();
        $users = $users->pluck('id')->random($seed_count)->shuffle();
        $group_data = [];
        $group_id = ($group_id ?? 0) + 1;
        $data = [];
        $now = now();
        foreach ($users as $user) {
            $join_at = $faker->dateTimeBetween($start_at, $end_at);
            $data[] = [
                'user_id' => $user,
                'model' => $this->getTable(),
                'model_id' => $this->getKey(),
                'group_id' => $group_id,
                'join_at' => $join_at,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $group_data[] = $user;
            // 组id
            if (count($group_data) >= $member_count) {
                $group_id += 1;
                $group_data = [];
            }
        }

        VirtualActivityUser::query()
            ->insert($data);

        return true;
    }
}
