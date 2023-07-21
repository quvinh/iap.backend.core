<?php

namespace Database\Seeders\development;

use App\Models\CategoryPurchase;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoryPurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            'Hàng hoá',
            'Dịch vụ',
            'Nguyên vật liệu',
        ];

        foreach ($types as $type) {
            CategoryPurchase::create([
                'name' => $type,
                'created_by' => 'seeder'
            ]);
        }
    }
}
