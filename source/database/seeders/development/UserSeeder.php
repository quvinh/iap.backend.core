<?php

namespace Database\Seeders\development;

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
        for ($i = 0; $i < 10; $i++) {
            $permission_name = "permission.admin.{$i}";
            $id = DB::table('permissions')->insertGetId([
                'name' => $permission_name,
                'created_by' => 'seeder'
            ]);
            $permission_groups = PermissionGroupFactory::new()->forRole(UserRoles::ADMINISTRATOR)->forPermission($id)->make();
            DB::table('permission_groups')->insert($permission_groups->getAttributes());
        }
        for ($i = 0; $i < 5; $i++) {
            $users = UserFactory::new()->forRole(UserRoles::ADMINISTRATOR)->make();
            $users->username = "admin.{$i}";
            DB::table('users')->insert($users->getAttributes());
        }

        // moderator
        Role::upsert([
            'id' => UserRoles::MODERATOR,
            'name' => 'Moderator',
            'created_by' => 'seeder'
        ], ['id']);
        for ($i = 0; $i < 5; $i++) {
            $permission_name = "permission.moderator.{$i}";
            $id = DB::table('permissions')->insertGetId([
                'name' => $permission_name,
                'created_by' => 'seeder'
            ]);
            $permission_groups = PermissionGroupFactory::new()->forRole(UserRoles::MODERATOR)->forPermission($id)->make();
            DB::table('permission_groups')->insert($permission_groups->getAttributes());
        }
        for ($i = 0; $i < 5; $i++) {
            $users = UserFactory::new()->forRole(UserRoles::MODERATOR)->make();
            $users->username = "moderator.{$i}";
            DB::table('users')->insert($users->getAttributes());
        }

        // member
        Role::upsert([
            'id' => UserRoles::MEMBER,
            'name' => 'Member',
            'created_by' => 'seeder'
        ], ['id']);
        for ($i = 0; $i < 5; $i++) {
            $permission_name = "permission.member.{$i}";
            $id = DB::table('permissions')->insertGetId([
                'name' => $permission_name,
                'created_by' => 'seeder'
            ]);
            $permission_groups = PermissionGroupFactory::new()->forRole(UserRoles::MEMBER)->forPermission($id)->make();
            DB::table('permission_groups')->insert($permission_groups->getAttributes());
        }
        for ($i = 0; $i < 5; $i++) {
            $users = UserFactory::new()->forRole(UserRoles::MEMBER)->make();
            $users->username = "member.{$i}";
            DB::table('users')->insert($users->getAttributes());
        }
    }
}
