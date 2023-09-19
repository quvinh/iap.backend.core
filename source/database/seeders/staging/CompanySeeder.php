<?php

namespace Database\Seeders\staging;

use App\Models\Company;
use Database\Factories\CompanyDetailFactory;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dataCompanies = [
            [
                'name' => 'Công ty Công nghệ số và In đồ họa',
                'tax_code' => '0200638946',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Tnhh Du Lịch An Biên',
                'tax_code' => '0200937801',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Cổ Phần Gỗ Ngọc Đại',
                'tax_code' => '0201641042',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Tnhh Thương Mại Dịch Vụ Giang Khánh Sơn',
                'tax_code' => '0201988044',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Tnhh Bánh Mứt Thanh Lịch',
                'tax_code' => '0201738132',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Tnhh Kinh Doanh Thương Mại Dịch Vụ Thuận Phong',
                'tax_code' => '0201987259',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Tnhh Đại Lý Thuế Miền Bắc',
                'tax_code' => '0201762689',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Cổ Phần Đầu Tư Phát Triển Minh Hải',
                'tax_code' => '0201429550',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Tnhh Ô Tô Hưng Tiến',
                'tax_code' => '0200505329',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Tnhh Tin Học Và Ngoại Ngữ THL',
                'tax_code' => '0202015778',
                'created_by' => 'seeder',
            ],
        ];

        # Insert
        foreach ($dataCompanies as $row) {
            Company::create($row);
        }
    }
}
