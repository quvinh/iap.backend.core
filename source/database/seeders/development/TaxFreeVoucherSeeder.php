<?php

namespace Database\Seeders\development;

use App\Models\TaxFreeVoucher;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaxFreeVoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            ['name' => 'BHXH Bán hàng', 'number_account' => '641'],
            ['name' => 'BHXH Nhân công', 'number_account' => '622'],
            ['name' => 'BHXH Quản lý', 'number_account' => '642'],
            ['name' => 'BHXH Quản lý SX', 'number_account' => '627'],
            ['name' => 'Chi phí khác', 'number_account' => '811'],
            ['name' => 'Chi phí lãi vay', 'number_account' => '635'],
            ['name' => 'CP dịch vụ bán hàng', 'number_account' => '641'],
            ['name' => 'CP thuê nhà,kho,phương tiện Bán hàng', 'number_account' => '641'],
            ['name' => 'CP thuê nhà,kho,phương tiện CP Chung', 'number_account' => '627'],
            ['name' => 'CP thuê nhà,kho,phương tiện Quản lý', 'number_account' => '642'],
            ['name' => 'CP trả trước Bán hàng', 'number_account' => '641'],
            ['name' => 'CP trả trước CP Chung', 'number_account' => '627'],
            ['name' => 'CP trả trước Quản lý', 'number_account' => '642'],
            ['name' => 'CP VPP Quản lý', 'number_account' => '642'],
            ['name' => 'KH CCDC Bán hàng', 'number_account' => '641'],
            ['name' => 'KH CCDC Quản lý', 'number_account' => '642'],
            ['name' => 'KH CCDC Sản xuất', 'number_account' => '627'],
            ['name' => 'KH TSCĐ Bán hàng', 'number_account' => '641'],
            ['name' => 'KH TSCĐ CP Chung', 'number_account' => '627'],
            ['name' => 'KH TSCĐ Quản lý', 'number_account' => '642'],
            ['name' => 'Lương Bán hàng', 'number_account' => '641'],
            ['name' => 'Lương CP Chung', 'number_account' => '627'],
            ['name' => 'Lương Nhân Công', 'number_account' => '622'],
            ['name' => 'Lương Quản lý', 'number_account' => '642'],
            ['name' => 'NC thuê ngoài Bán hàng', 'number_account' => '641'],
            ['name' => 'NC thuê ngoài CP Chung', 'number_account' => '627'],
            ['name' => 'NC thuê ngoài Quản lý', 'number_account' => '642'],
            ['name' => 'Thu nhập khác', 'number_account' => '711'],
        ];

        foreach ($items as $item) {
            TaxFreeVoucher::create([
                'name' => $item['name'],
                'number_account' => $item['number_account'],
                'created_by' => 'seeder'
            ]);
        }
    }
}
