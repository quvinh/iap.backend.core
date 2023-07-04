<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->company(),
            'tax_code' => fake()->numerify('##########'), // 10 digit faker
            'tax_password' => fake()->numerify('######'), // 6 digit faker
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'manager_name' => fake()->name(),
            'manager_role' => fake()->asciify('******'),
            'manager_phone' => fake()->phoneNumber(),
            'manager_email' => fake()->unique()->safeEmail(),
            'created_by' => 'seeder',
        ];
    }
}
