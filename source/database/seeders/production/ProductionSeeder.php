<?php

namespace Database\Seeders\production;

use Database\Seeders\AdministrativeLocationSeeder;
use Database\Seeders\development\UserSeeder;
use Illuminate\Support\Facades\DB;

class ProductionSeeder extends \Illuminate\Database\Seeder
{

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $this->call([]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
