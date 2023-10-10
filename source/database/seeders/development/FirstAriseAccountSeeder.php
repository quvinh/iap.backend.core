<?php

namespace Database\Seeders\development;

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
                'number_percent' => 1,
            ],
            [
                'name' => 'Máy công trình',
                'number_account' => null,
                'number_percent' => 1,
            ],
            [
                'name' => 'CP chung',
                'number_account' => '154',
                'number_percent' => 1,
            ],
            [
                'name' => 'Tài chính',
                'number_account' => '635',
                'number_percent' => 1,
            ],
            [
                'name' => 'Bán hàng',
                'number_account' => '641',
                'number_percent' => 1,
            ],
            [
                'name' => 'Quản lý',
                'number_account' => '642',
                'number_percent' => 1,
            ],
            [
                'name' => 'Vay & nợ',
                'number_account' => '341',
                'number_percent' => 0,
            ],
            [
                'name' => 'TSCĐ',
                'number_account' => '211',
                'number_percent' => 0,
            ],
            [
                'name' => 'CCDC',
                'number_account' => '153',
                'number_percent' => 0,
            ],
            [
                'name' => 'Trả trước',
                'number_account' => '242',
                'number_percent' => 0,
            ],
            [
                'name' => 'Chi phí khác',
                'number_account' => '811',
                'number_percent' => 0,
            ],
            [
                'name' => 'Thu nhập khác',
                'number_account' => '711',
                'number_percent' => 0,
            ],
            [
                'name' => 'CP loại trừ',
                'number_account' => null,
                'number_percent' => 0,
            ],
        ];

        foreach ($accounts as $account) {
            FirstAriseAccount::create([
                'name' => $account['name'],
                'number_account' => $account['number_account'],
                'number_percent' => $account['number_percent'],
            ]);
        }
    }
}
