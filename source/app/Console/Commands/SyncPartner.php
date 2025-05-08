<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncPartner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:partner {--chunk=500}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync partner';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Vô hiệu hóa timeout và cấp tối đa 1 GB RAM cho PHP-CLI
        set_time_limit(0);
        ini_set('memory_limit', '1G');

        $chunkSize = (int) $this->option('chunk');
        $this->info("Bắt đầu xử lý với mỗi lô {$chunkSize} bản ghi...");

        Invoice::chunkById($chunkSize, function ($rows) {
            $data = [];
            $now  = Carbon::now();

            foreach ($rows as $row) {
                $data[] = [
                    'company_id' => $row->company_id,
                    'name' => $row->partner_name,
                    'tax_code' => $row->partner_tax_code,
                    'email' => null,
                    'phone' => null,
                    'address' => $row->partner_address,
                    'logo' => null,
                    'created_by' => 'command',
                    'updated_by' => 'command',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // DB::table('business_partners')->insert($data);

            $firstId = $rows->first()->id;
            $lastId  = $rows->last()->id;
            $peakMb  = round(memory_get_peak_usage() / 1024 / 1024, 2);

            $this->info("Processed IDs {$firstId}–{$lastId} (".count($rows)." records)");
            $this->info("Peak memory usage: {$peakMb} MB");

            // Giải phóng bộ nhớ ngay
            unset($data);
            gc_collect_cycles();
        });

        $this->info("✅ Completed");
        return Command::SUCCESS;
    }
}
