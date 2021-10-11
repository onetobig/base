<?php

namespace App\Console\Commands\Api;

use App\Models\User;
use Illuminate\Console\Command;

class CreateApiToken extends Command
{
    protected $signature = 'api:generate-token';

    protected $description = 'Generate Api Token，Expires at 1 Years';

    public function handle()
    {
        $user_id = $this->ask('user_id：', 1);
        $user = User::query()->find($user_id);
        if (!$user) {
            return $this->info('User Not Find');
        }
        $token = $user->createToken('lshop')->accessToken;
        return $this->info($token);
    }
}
