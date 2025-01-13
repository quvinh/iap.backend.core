<?php

namespace App\Console\Commands;

use App\Models\CompanyDocument;
use Illuminate\Console\Command;

class SyncDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:documents {year}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync documents to gdrive';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->syncDocuments($this->argument('year'));
    }

    private function syncDocuments($year) {
        $records = CompanyDocument::where('year', $year)->get(['id', 'file']);
        foreach ($records as $record) {
            $this->info($record->file);
        }
    }
}
