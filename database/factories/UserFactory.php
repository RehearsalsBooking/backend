<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'public_email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->e164PhoneNumber,
            'link' => $this->faker->url,
            'avatar' => 'https://picsum.photos/100/100',
            'email_verified_at' => now(),
            'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
            'remember_token' => $this->faker->randomKey,
        ];
    }
}
