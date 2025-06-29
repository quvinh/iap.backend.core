<?php

namespace App\Jobs;

use App\Helpers\Common\MetaInfo;
use App\Helpers\Utils\StorageHelper;
use App\Imports\ImportedGoodsImport;
use App\Imports\ItemCodeImport;
use App\Models\JobHistory;
use App\Services\Invoice\IInvoiceService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class InvoiceImportExcelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected int $company_id;
    protected int $year;
    protected int $user_id;
    protected int $job_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($filePath, array $params, int $user_id, int $job_id)
    {
        $this->queue = 'excel'; # Queue command: `php artisan queue:listen --queue=excel` or `php artisan queue:work --queue=excel`
        $this->delay = now()->addSeconds(1); # Delay 1 seconds

        $this->filePath = $filePath;
        $this->company_id = $params['company_id'];
        $this->year = $params['year'];
        $this->user_id = $user_id;
        $this->job_id = $job_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('👉 Starting import...');

        $jobId = $this->job->getJobId() ?? null;
        $filePath = Storage::disk(StorageHelper::TMP_DISK_NAME)->path($this->filePath);

        JobHistory::find($this->job_id)->update([
            'job_id' => $jobId,
            'path' => $this->filePath,
            'status' => JobHistory::STATUS_PROCESSING,
            'note' => 'Đang xử lý',
        ]);
        
        Excel::import(new ItemCodeImport(
            $this->company_id, 
            $this->year, 
            $this->user_id, 
            $this->job_id, 
        ), $filePath);

        // if (Storage::disk(StorageHelper::TMP_DISK_NAME)->exists($this->filePath)) {
        //     Storage::disk(StorageHelper::TMP_DISK_NAME)->delete($this->filePath);
        // }
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed(Throwable $exception)
    {
        Log::error($exception->getMessage());
        JobHistory::find($this->job_id)->update([
            'status' => JobHistory::STATUS_ERROR,
            'note' => $exception->getMessage(),
        ]);
    }
}
