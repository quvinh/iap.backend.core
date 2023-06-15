<?php

namespace Database\Seeders\development;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DevelopmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('permission_groups')->truncate();
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();
        DB::table('user_companies')->truncate();
        DB::table('users')->truncate();
        DB::table('company_details')->truncate();
        DB::table('companies')->truncate();
        DB::table('company_types')->truncate();
        DB::table('company_detail_arise_accounts')->truncate();
        DB::table('first_arise_accounts')->truncate();
        
        $this->call([
            UserSeeder::class,
            CompanyTypeSeeder::class,
            FirstAriseAccountSeeder::class
        ]);
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
