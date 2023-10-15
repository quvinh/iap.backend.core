<?php

namespace Database\Seeders\development;

use App\Helpers\Enums\CategoryActions;
use App\Helpers\Enums\CategoryTags;
use App\Models\CategorySold;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySoldSeeder extends Seeder
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
                'name' => 'Thành phẩm',
                'tag' => CategoryTags::MATERIAL,
                'method' => CategoryActions::PLUS,
            ],
            [
                'name' => 'Hàng hoá bán ra',
                'tag' => CategoryTags::COMMODITY,
                'method' => CategoryActions::PLUS,
            ],
            [
                'name' => 'Dịch vụ bán ra',
                'tag' => null,
                'method' => CategoryActions::PLUS,
            ],
            [
                'name' => 'Thu nhập khác',
                'tag' => null,
                'method' => CategoryActions::PLUS,
            ],
            [
                'name' => 'Khuyến mãi',
                'tag' => null,
                'method' => CategoryActions::IGNORE,
            ],
        ];

        foreach ($types as $type) {
            CategorySold::create([
                'name' => $type['name'],
                'tag' => $type['tag'],
                'method' => $type['method'],
                'created_by' => 'seeder'
            ]);
        }
    }
}
