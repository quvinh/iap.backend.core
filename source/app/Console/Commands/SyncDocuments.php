<?php

namespace App\Console\Commands;

use App\Helpers\Utils\StorageHelper;
use App\Models\CompanyDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

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
        if (count($records) > 0) {
            foreach ($records as $record) {
                if (isset($record->file)) {
                    $this->transferToGDrive($record->file);
                }
            }
        } else {
            $this->info('No record');
        }
    }

    private function transferToGDrive(string $path) {
        $storage = Storage::disk(StorageHelper::TMP_DISK_NAME);
        if ($storage->exists($path)) {
            $this->info("✅ $path");
            if (StorageHelper::moveFile($path, StorageHelper::TMP_DISK_NAME, StorageHelper::CLOUD_DISK_NAME, forceDelete: false)) {
                $this->info('Upload successfully');
            } else {
                $this->error('Upload failed');
            }
        } else {
            $this->info("⛔ $path");
        }
    }
}
