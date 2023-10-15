<?php

namespace Database\Seeders\development;

use App\Helpers\Enums\CategoryActions;
use App\Models\FirstAriseAccount;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FirstAriseAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $accounts = [
            [
                'name' => 'Nhân công',
                'number_account' => '154',
                'method' => CategoryActions::PLUS,
                'number_percent' => 1,
            ],
            [
                'name' => 'Máy công trình',
                'number_account' => null,
                'method' => CategoryActions::PLUS,
                'number_percent' => 1,
            ],
            [
                'name' => 'CP chung',
                'number_account' => '154',
                'method' => CategoryActions::PLUS,
                'number_percent' => 1,
            ],
            [
                'name' => 'Tài chính',
                'number_account' => '635',
                'method' => CategoryActions::PLUS,
                'number_percent' => 1,
            ],
            [
                'name' => 'Bán hàng',
                'number_account' => '641',
                'method' => CategoryActions::PLUS,
                'number_percent' => 1,
            ],
            [
                'name' => 'Quản lý',
                'number_account' => '642',
                'method' => CategoryActions::PLUS,
                'number_percent' => 1,
            ],
            [
                'name' => 'Vay & nợ',
                'number_account' => '341',
                'method' => CategoryActions::PLUS,
                'number_percent' => 0,
            ],
            [
                'name' => 'TSCĐ',
                'number_account' => '211',
                'method' => CategoryActions::PLUS,
                'number_percent' => 0,
            ],
            [
                'name' => 'CCDC',
                'number_account' => '153',
                'method' => CategoryActions::PLUS,
                'number_percent' => 0,
            ],
            [
                'name' => 'Trả trước',
                'number_account' => '242',
                'method' => CategoryActions::PLUS,
                'number_percent' => 0,
            ],
            [
                'name' => 'Chi phí khác',
                'number_account' => '811',
                'method' => CategoryActions::PLUS,
                'number_percent' => 0,
            ],
            [
                'name' => 'Thu nhập khác',
                'number_account' => '711',
                'method' => CategoryActions::PLUS,
                'number_percent' => 0,
            ],
            [
                'name' => 'CP loại trừ',
                'number_account' => null,
                'method' => CategoryActions::SUB,
                'number_percent' => 0,
            ],
        ];

        foreach ($accounts as $account) {
            FirstAriseAccount::create([
                'name' => $account['name'],
                'number_account' => $account['number_account'],
                'number_percent' => $account['number_percent'],
                'method' => $account['method'],
                'created_by' => 'seeder',
            ]);
        }
    }
}
