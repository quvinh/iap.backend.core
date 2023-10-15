<?php

namespace Database\Seeders\development;

use App\Helpers\Enums\CategoryTags;
use App\Models\CategoryPurchase;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Helpers\Enums\CategoryActions;

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
            [
                'name' => 'Hàng hoá',
                'tag' => CategoryTags::COMMODITY,
                'method' => CategoryActions::PLUS,
            ],
            [
                'name' => 'Dịch vụ',
                'tag' => null,
                'method' => CategoryActions::PLUS,
            ],
            [
                'name' => 'Nguyên vật liệu',
                'tag' => CategoryTags::MATERIAL,
                'method' => CategoryActions::PLUS,
            ],
        ];

        foreach ($types as $type) {
            CategoryPurchase::create([
                'name' => $type['name'],
                'tag' => $type['tag'],
                'method' => $type['method'],
                'created_by' => 'seeder'
            ]);
        }
    }
}
