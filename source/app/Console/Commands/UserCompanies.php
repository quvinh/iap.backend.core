<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Console\Command;

class UserCompanies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:companies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set user_id = 3';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $user = User::find(3);
            if (empty($user)) $this->error("User with ID: 3 not found!");
            else {
                UserCompany::query()->where('user_id', $user->id)->delete();
                $companies = Company::all(['id']);
                foreach ($companies as $company) {
                    UserCompany::create([
                        'user_id' => $user->id,
                        'company_id' => $company->id,
                    ]);
                }
                $this->info("Done!");
            }
        } catch (\Exception $ex) {
            $this->error($ex->getMessage());
        }
    }
}
