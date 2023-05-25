<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PermissionGroup>
 */
class PermissionGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            //
        ];
    }

    public function forRole($id)
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => $id
        ]);
    }

    public function forPermission($id)
    {
        return $this->state(fn (array $attributes) => [
            'permission_id' => $id
        ]);
    }
}
