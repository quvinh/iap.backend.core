<?php

namespace Database\Seeders\development;

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
        for ($i = 0; $i < 40; $i++) {
            $company = Company::create(CompanyFactory::new()->make()->toArray());
            $company_type_id = rand(0, 7);
            $company_detail = CompanyDetailFactory::new()
                ->forCompany($company->id)
                ->forCompanyType($company_type_id)
                ->forYear(date('Y'))
                ->make();

            $company_detail_last = CompanyDetailFactory::new()
                ->forCompany($company->id)
                ->forCompanyType($company_type_id)
                ->forYear(date('Y') - 1)
                ->make();
            
            DB::table('company_details')->insert($company_detail_last->toArray());
            DB::table('company_details')->insert($company_detail->toArray());
        }
    }
}
