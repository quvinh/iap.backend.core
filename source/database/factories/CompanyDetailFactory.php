<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CompanyDetail>
 */
class CompanyDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'description' => fake()->text(100)
        ];
    }

    public function forCompany($id)
    {
        return $this->state(fn (array $attribute) => [
            'company_id' => $id
        ]);
    }

    public function forCompanyType($id)
    {
        return $this->state(fn (array $attribute) => [
            'company_type_id' => $id
        ]);
    }

    public function forYear($year)
    {
        return $this->state(fn (array $attribute) => [
            'year' => $year
        ]);
    }
}
