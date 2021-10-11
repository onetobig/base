<?php
namespace Database\Factories;

use \Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    function definition()
    {
        $faker = $this->faker;
        return [
            'nickname' => $faker->userName,
            'phone' => $faker->phoneNumber,
            'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
            'remember_token' => str_random(10),
        ];
    }
}
