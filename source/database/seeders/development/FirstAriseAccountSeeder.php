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
                'number_account' => '154'
            ],
            [
                'name' => 'Máy công trình',
                'number_account' => null
            ],
            [
                'name' => 'CP chung',
                'number_account' => '154'
            ],
            [
                'name' => 'Tài chính',
                'number_account' => '635'
            ],
            [
                'name' => 'Bán hàng',
                'number_account' => '642'
            ],
            [
                'name' => 'Quản lý',
                'number_account' => '642'
            ],
            [
                'name' => 'Vay & nợ',
                'number_account' => '341'
            ],
            [
                'name' => 'TSCĐ',
                'number_account' => '211'
            ],
            [
                'name' => 'CCDC',
                'number_account' => '153'
            ],
            [
                'name' => 'Trả trước',
                'number_account' => '242'
            ],
            [
                'name' => 'Chi phí khác',
                'number_account' => '811'
            ],
            [
                'name' => 'Thu nhập khác',
                'number_account' => '711'
            ],
            [
                'name' => 'CP loại trừ',
                'number_account' => null
            ],
        ];

        foreach ($accounts as $account) {
            FirstAriseAccount::create([
                'name' => $account['name'],
                'number_account' => $account['number_account']
            ]);
        }
    }
}
