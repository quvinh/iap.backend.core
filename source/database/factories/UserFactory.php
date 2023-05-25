<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make(md5('password')), // password
            'remember_token' => Str::random(10),
            'phone' => fake()->phoneNumber(),
            'birthday' => fake()->date(),
            'address' => fake()->address()
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function forRole($id)
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => $id
        ]);
    }

    public function withPhoto($url)
    {
        return $this->state(fn (array $attributes) => [
            'photo' => $url
        ]);
    }
}
