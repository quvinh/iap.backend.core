<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Database\Seeders\development\DevelopmentSeeder;
use Database\Seeders\production\ProductionSeeder;
use Database\Seeders\staging\StagingSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        switch (config('app.env')) {
            case 'production':
                $this->call([ProductionSeeder::class]);
                break;
            case 'staging':
                $this->call([StagingSeeder::class]);
                break;
            default:
                $this->call([DevelopmentSeeder::class]);
        }
    }
}
