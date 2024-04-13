<?php

namespace App\Console\Commands;

use App\Helpers\Utils\StorageHelper;
use App\Services\PdfExport\TCPDFService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test-command {case}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    protected $cases = ['generate-pdf'];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        switch ($this->argument('case')) {
            case 'generate-pdf':
                $this->generatePDF();
                break;
            default:
                $this->error('Command invalid');
                break;
        }
    }

    private function generatePDF()
    {
        $this->info('Starting generate PDF');
        $storage = Storage::disk(StorageHelper::TMP_DISK_NAME);
        $ymd = Carbon::now()->format('Ymd');
        $folder = "pdfs/$ymd";
        if (!$storage->exists($folder)) $storage->makeDirectory($folder);

        $pdf = new TCPDFService('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->configDefault();
        // set auto page breaks false
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->setPrintHeader(true);
        $pdf->setPrintFooter(false);
        // add a page
        $pdf->AddPage('P', 'A4');
        $pdf->writeHTML("<h1>Test pdf</h1>", true, false, true, false, '');
        $pdf->Output($storage->path("$folder/TestPdf.pdf"), 'F');
    }
}
