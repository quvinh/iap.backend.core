<?php

namespace Database\Seeders\staging;

use App\Helpers\Enums\UserRoles;
use App\Models\Role;
use Database\Factories\PermissionGroupFactory;
use Database\Factories\UserFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // administrator
        Role::upsert([
            'id' => UserRoles::ADMINISTRATOR,
            'name' => 'Administrator',
            'created_by' => 'seeder'
        ], ['id']);
        # TODO:Need add permission
        $users = UserFactory::new()->forRole(UserRoles::ADMINISTRATOR)->make();
        $users->username = "admin";
        DB::table('users')->insert($users->getAttributes());
    }
}