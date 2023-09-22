<?php

namespace Database\Seeders\staging;

use App\Models\Company;
use App\Models\CompanyDetail;
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
                'name' => 'Công ty Cổ phẩn Dev-test',
                'tax_code' => '1234567890',
                'created_by' => 'seeder',
            ],
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
            [
                'name' => 'Công Ty Tnhh Nivaco',
                'tax_code' => '0202030367',
                'created_by' => 'seeder',
            ],
            [
                'name' => '	Công Ty Tnhh Thương Mại Công Nghệ Cao MB',
                'tax_code' => '0202017253',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Trách Nhiệm Hữu Hạn Hoàng Châu',
                'tax_code' => '0200392178',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Tnhh Sản Xuất Dịch Vụ Và Thương Mại An Nguyên',
                'tax_code' => '0202046800',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Cổ Phần Thương Mại Đầu Tư Và Vận Tải Phú An',
                'tax_code' => '0201050339',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Tnhh Thương Mại Điện Cơ Tân Tiến',
                'tax_code' => '0202077189',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Tnhh Phát Triển Thương Mại Hợp Lực',
                'tax_code' => '0201320560',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Tnhh Kỹ Thuật Công Nghệ Thương Mại Và Dịch Vụ Hd Hải Phòng',
                'tax_code' => '0202093254',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Cổ Phần Phát Tiển Thương Hiệu Toxebrand',
                'tax_code' => '0201175433',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công ty TNHH Meta Pro',
                'tax_code' => '0201985389',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Tnhh Vật Tư Dịch Vụ Minh Thảo',
                'tax_code' => '0202109472',
                'created_by' => 'seeder',
            ],
            [
                'name' => '	Hợp Tác Xã Nam Phong',
                'tax_code' => '0200648863',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Cổ Phần Tư Vấn Thiết Kế Và Đầu Tư Xây Dựng Hp Nam Việt',
                'tax_code' => '0202129327',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Tnhh Kinh Doanh Và Xây Dựng Thành An',
                'tax_code' => '0202131090',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Tnhh Happy Goods',
                'tax_code' => '0202110767',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Tnhh Đầu Tư Thương Mại Và Phát Triển Smc',
                'tax_code' => '0202111979',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Cổ Phần Thương Mại Và Dịch Vụ Trường Hoa',
                'tax_code' => '0201212614',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Tnhh Thương Mại Xuất Nhập Khẩu Tuấn Hưng',
                'tax_code' => '0201117833',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Tnhh Giao Nhận Quốc Tế Minh Anh',
                'tax_code' => '0201180715',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Cổ Phần Thương Mại Gia Gia',
                'tax_code' => '0201099133',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Tnhh Vận Tải Đh Minh Phương',
                'tax_code' => '0202034072',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Tnhh Dũng Mon',
                'tax_code' => '0202044087',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Tnhh SX-TM Trần phúc Foods',
                'tax_code' => '0202025060',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Tnhh Vận Tải Hoàng Trí',
                'tax_code' => '0201191146',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Cổ Phần Đầu Tư Thương Mại Và Vận Tải Quốcc Tế Hoàng Anh',
                'tax_code' => '0201875770',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Tnhh Quảng Cáo Và Tuyền Thông Đức Lộc',
                'tax_code' => '0109097270',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Tnhh Thương Mại Điện Tử Điện Lạnh Minh Ngân',
                'tax_code' => '0202084683',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Tnhh Ces Media',
                'tax_code' => '0202091296',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Cổ Phần Đại Tỉnh',
                'tax_code' => '0201558059',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Cổ Phần Quảng Cáo Và Dịch Vụ Truyền Thông Tân Thành Phát',
                'tax_code' => '0202101265',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Tnhh Đầu Tư Thương Mại Và Vận Tải Nhật Nam',
                'tax_code' => '0201254491',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Tnhh Thương Mại Và Xây Lắp Gia Huy',
                'tax_code' => '0201906718',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Cổ Phần Giải Pháp An Ninh Việt',
                'tax_code' => '0201803624',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Tnhh Đầu Tư Phát Triển Thương Mại Và Vận Tải Khải Nguyên',
                'tax_code' => '0200971489',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Tnhh Phòng Cháy Chữa Cháy Phú An',
                'tax_code' => '0201806720',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Tnhh Fansipan IFC',
                'tax_code' => '0202130467',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Tnhh Dịch Vụ Thương Mại Xnk Hùng Cường',
                'tax_code' => '0202155817',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Tnhh Thương Mại Dịch Vụ Du Lịch Phúc Hân',
                'tax_code' => '0201986706',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công Ty Cp Thép BS',
                'tax_code' => '0202166745',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công ty cổ phần Thiết bị xây dựng Bảo Sơn',
                'tax_code' => '0202167347',
                'created_by' => 'seeder',
            ],
            [
                'name' => 'Công ty TNHH Thương mại dịch vụ kỹ thuật Quốc Việt Nhật',
                'tax_code' => '0201818518',
                'created_by' => 'seeder',
            ],
            [
                'name' => '	Công ty TNHH Song Dung',
                'tax_code' => '0200573590',
                'created_by' => 'seeder',
            ],
        ];

        # Insert
        foreach ($dataCompanies as $row) {
            $com = Company::create($row);
            CompanyDetail::create([
                'company_id' => $com->id,
                'company_type_id' => rand(1, 8),
                'year' => date('Y'),
            ]);
        }
    }
}
