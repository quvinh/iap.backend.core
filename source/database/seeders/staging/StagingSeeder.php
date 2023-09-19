<?php

namespace Database\Seeders\staging;

use Database\Seeders\AdministrativeLocationSeeder;
use Database\Seeders\development\CategoryPurchaseSeeder;
use Database\Seeders\development\CategorySoldSeeder;
use Database\Seeders\development\CompanyTypeSeeder;
use Database\Seeders\development\FirstAriseAccountSeeder;
use Database\Seeders\development\TaxFreeVoucherSeeder;
use Database\Seeders\staging\UserSeeder as StagingUserSeeder;
use Illuminate\Support\Facades\DB;

class StagingSeeder extends \Illuminate\Database\Seeder
{

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
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
        DB::table('tax_free_vouchers')->truncate();
        $this->call([
            StagingUserSeeder::class,
            CompanyTypeSeeder::class,
            FirstAriseAccountSeeder::class,
            CompanySeeder::class,
            TaxFreeVoucherSeeder::class,
            CategoryPurchaseSeeder::class,
            CategorySoldSeeder::class,
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
