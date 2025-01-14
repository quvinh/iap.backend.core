<?php

namespace App\Console\Commands;

use App\Helpers\Utils\StorageHelper;
use App\Models\CompanyDocument;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        DB::beginTransaction();
        try {
            $records = CompanyDocument::where('year', $year)->whereNull('drive')->get(['id', 'file']);
            if (count($records) > 0) {
                foreach ($records as $record) {
                    if (isset($record->file)) {
                        // $this->testRetrieveFile($record->file);
                        if($this->transferToGDrive($record->file)) {
                            $record->drive = CompanyDocument::DRIVE_GOOGLE;
                            $record->save();
                        } else {
                            $record->drive = CompanyDocument::DRIVE_LOCAL;
                            $record->save();
                        }
                    }
                }
            } else {
                $this->info('No record');
            }
            DB::commit();
            $this->info('Done');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            DB::rollBack();
        }
    }

    private function transferToGDrive(string $path) {
        $storage = Storage::disk(StorageHelper::TMP_DISK_NAME);
        if ($storage->exists($path)) {
            $this->info("✅ $path");
            if (StorageHelper::moveFile($path, StorageHelper::TMP_DISK_NAME, StorageHelper::CLOUD_DISK_NAME, forceDelete: false)) {
                $this->info('Upload successfully');
                return true;
            } else {
                $this->error('Upload failed');
                throw new Exception('Upload gdrive failed');
            }
        } else {
            $this->info("⛔ $path");
        }
        return false;
    }

    private function testRetrieveFile(string $path) {
        $result = StorageHelper::testRetrieveFile(StorageHelper::CLOUD_DISK_NAME, $path);
        if (empty($result)) $this->info("⛔ $path");
        else $this->info("✅ $result");
    }
}
