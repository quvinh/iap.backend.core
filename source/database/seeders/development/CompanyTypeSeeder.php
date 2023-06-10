<?php

namespace Database\Seeders\development;

use App\Models\CompanyType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompanyTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            'Ăn uống',
            'Dịch vụ',
            'Khách sạn, nhà nghỉ',
            'Nông lâm ngư nghiệp',
            'Sản xuất - chế biến',
            'Thương mại',
            'Vận tải',
            'Xây dựng'
        ];

        foreach ($types as $type) {
            CompanyType::create([
                'name' => $type,
                'created_by' => 'seeder'
            ]);
        }
    }
}
