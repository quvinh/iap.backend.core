<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class FreshUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:fresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $this->info('Preparing update');
            $firstUser = User::find(1);
            $firstUser->update([
                'name' => 'Ngô Quang Vinh',
                'email' => 'vinhhp2620@gmail.com',
                'username' => 'vinhnq',
            ]);
            $this->info("done: {$firstUser->name}");
            $secondUser = User::find(2);
            $secondUser->update([
                'name' => 'Lê Xuân Hiến',
                'username' => 'lxhien',
            ]);
            $this->info("done: {$secondUser->name}");
        } catch (\Exception $ex) {
            $this->error($ex->getMessage());
        }
    }
}
