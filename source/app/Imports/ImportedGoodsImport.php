<?php

namespace App\Imports;

use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\InvoiceCurrencies;
use App\Helpers\Utils\DateHelper;
use App\Models\Department;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\InvoiceTask;
use App\Models\ItemCode;
use App\Models\JobHistory;
use App\Services\Invoice\IInvoiceService;
use App\Services\Invoice\InvoiceService;
use Carbon\Carbon;
use Exception;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImportedGoodsImport implements ToCollection, WithHeadingRow, WithStartRow
{
    protected $batchData = [];
    protected int $imported = 0;
    private int $user_id;
    private int $company_id;
    private string $invoice_type;
    private string $job_id;
    private int $year;
    private $cacheKey;
    private IInvoiceService $invoiceService;
    private ?MetaInfo $commandMetaInfo;

    public function __construct(IInvoiceService $invoiceService, int $company_id, int $year, string $invoice_type, int $user_id, int $job_id, ?MetaInfo $commandMetaInfo)
    {
        $this->company_id = $company_id;
        $this->year = $year;
        $this->invoice_type = $invoice_type;
        $this->user_id = $user_id;
        $this->job_id = $job_id;
        // $this->user_id = auth()->user()->getAuthIdentifier();
        $this->cacheKey = "company_{$this->company_id}_year_{$this->year}_product_codes";
        $this->initializeCache();
        $this->invoiceService = $invoiceService;
        $this->commandMetaInfo = $commandMetaInfo;
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function startRow(): int
    {
        return 5;
    }

    public function collection(Collection $collection)
    {
        foreach ($collection as $row) {
            $this->importRow($row->toArray());
        }

        if ($this->imported == 0) {
            // throw new Exception('Không có bản ghi nào được thêm');
            $note = '🛑 Không có bản ghi nào được thêm';
            Log::info($note);
        } else {
            $note = '✅ Hoàn thành';
            Log::info('👌 Imported successfully');
        }
        JobHistory::find($this->job_id)->update([
            'status' => JobHistory::STATUS_DONE,
            'note' => $note,
        ]);
    }

    protected function importRow(array $row)
    {
        $invoice_number_form = $row['invoice_number_form'] ?? null;
        $invoice_symbol = $row['invoice_symbol'] ?? null;
        $invoice_number = $row['invoice_number'] ?? null;
        $date = $row['date'] ?? null;
        $partner_name = $row['partner_name'] ?? null;
        $partner_tax_code = $row['partner_tax_code'] ?? null;
        $product = $row['product'] ?? null;
        $unit = $row['unit'] ?? null;
        $quantity = $row['quantity'] ?? 0;
        $price = $row['price'] ?? 0;
        $currency = $row['currency'] ?? InvoiceCurrencies::VND;
        $currency_price = $row['currency_price'] ?? 1;
        $isf_price = $row['isf_price'] ?? 0;
        $isf_currency = $row['isf_currency'] ?? InvoiceCurrencies::VND;
        $isf_currency_price = $row['isf_currency_price'] ?? 1;
        $import_tax = $row['import_tax'] ?? 0;
        $special_consumption_tax = $row['special_consumption_tax'] ?? 0;
        $vat = $row['vat'] ?? 0;

        if (
            empty($date) ||
            empty($partner_tax_code) ||
            empty($invoice_number) ||
            empty($invoice_symbol) ||
            empty($product) ||
            empty($unit)
        ) {
            return;
        }

        $date = DateHelper::convertDate($date);
        $unit = Str::lower($unit);
        $currency = Str::lower($currency);
        $isf_currency = Str::lower($isf_currency);

        $item_code_id = $this->findProductCodeBySimilarity($product, 50);

        # 1.Check task
        $task_month = Carbon::parse($date)->format('m/Y');
        $year = Carbon::parse($date)->format('Y');
        $task = InvoiceTask::query()->where([
            ['company_id', $this->company_id],
            ['month_of_year', $task_month],
        ])->first();

        if (empty($task)) {
            $task = InvoiceTask::create([
                'company_id' => $this->company_id,
                'month_of_year' => $task_month,
            ]);
        }

        # 2.Check invoice
        $invoice = Invoice::query()->where([
            ['company_id', $this->company_id],
            ['partner_tax_code', $partner_tax_code],
            ['type', $this->invoice_type],
            ['invoice_number', $invoice_number],
            ['invoice_symbol', $invoice_symbol],
            ['invoice_task_id', $task->id],
        ])->whereYear('date', $year)->first();

        if (empty($invoice)) {
            $invoice = new Invoice();
            $invoice->company_id = $this->company_id;
            $invoice->invoice_task_id = $task->id;
            $invoice->partner_tax_code = $partner_tax_code;
            $invoice->partner_name = $partner_name;
            $invoice->partner_address = null;
            $invoice->type = $this->invoice_type;
            $invoice->invoice_number = $invoice_number;
            $invoice->invoice_symbol = $invoice_symbol;
            $invoice->date = $date;
            $invoice->invoice_number_form = $invoice_number_form;
            $invoice->verification_code_status = 0; // 👈 Not confirm

            $invoice->is_imported_goods = 1;
            $invoice->currency = $currency;
            $invoice->currency_price = $currency_price;
            $invoice->isf_currency = $isf_currency;
            $invoice->isf_currency_price = $isf_currency_price;
            $invoice->setMetaInfo($this->commandMetaInfo);
            $invoice->save();
        }

        # 3.Create InvoiceDetail
        $invoiceDetail = new InvoiceDetail();
        $invoiceDetail->invoice_id = $invoice->id;
        $invoiceDetail->item_code_id = $item_code_id;
        $invoiceDetail->product = $product;
        $invoiceDetail->unit = $unit;
        $invoiceDetail->setInvoiceDetail($quantity, $price, $vat);
        $invoiceDetail->isf_price = $isf_price;
        $invoiceDetail->import_tax = $import_tax;
        $invoiceDetail->special_consumption_tax = $special_consumption_tax;
        $invoiceDetail->save();

        # 4.Update sum value of invoice
        $invoice->plusMoneyInvoice($invoiceDetail->total_money, $invoiceDetail->vat);
        $invoice->isf_sum_fee += $invoiceDetail->isf_price; // 👈 Not confirm
        $invoice->save();

        $this->imported++;
    }

    protected function findProductCodeBySimilarity(string $product_name, int $threshold = 50): ?string
    {
        $allProductCodes = Cache::get($this->cacheKey, []);
        $similarProductCode = null;
        $maxSimilarity = 0;

        foreach ($allProductCodes as $item) {
            similar_text($item['product'], $product_name, $percent);
            if ($percent >= $threshold && $percent > $maxSimilarity) {
                $maxSimilarity = $percent;
                // $similarProductCode = $item['product_code'];
                // return $similarProductCode;
                $similarProductCode = $item['id'];
            }
        }

        return $similarProductCode;
    }

    protected function initializeCache()
    {
        if (Cache::has($this->cacheKey)) {
            return;
        }

        $allProductCodes = ItemCode::where('company_id', $this->company_id)
            ->where('year', $this->year)
            ->get(['id', 'product_code', 'product'])
            ->toArray();

        Cache::put($this->cacheKey, $allProductCodes, now()->addMinutes(10));
    }
}
