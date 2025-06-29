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

class InvoiceImport implements ToCollection, WithHeadingRow, WithStartRow
{
    protected $batchData = [];
    protected int $imported = 0;
    private int $user_id;
    private int $company_id;
    private int $year;
    private int $job_id;

    public function __construct(int $company_id, int $year, int $user_id, int $job_id)
    {
        $this->company_id = $company_id;
        $this->year = $year;
        $this->user_id = $user_id;
        $this->job_id = $job_id;
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function startRow(): int
    {
        return 4;
    }

    public function collection(Collection $collection)
    {
        foreach ($collection as $row) {
            $this->importRow($row->toArray());
        }

        // Insert any remaining batch data after the loop
        $this->finalizeImport();

        if ($this->imported == 0) {
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
        $price = floatval($row['price'] ?? 0);
        $invoice_number_form = $row['invoice_number_form'] ?? null;
        $invoice_symbol = $row['invoice_symbol'] ?? null;
        $invoice_number = $row['invoice_number'] ?? null;
        $date = $row['date'] ?? null;
        $partner_name = $row['partner_name'] ?? null;
        $partner_tax_code = $row['partner_tax_code'] ?? null;
        $product = $row['product'] ?? null;
        $product_exchange = $row['product_exchange'] ?? null;
        $product_code = $row['product_code'] ?? null;
        $unit = Str::title($row['unit'] ?? '/');
        $quantity = $row['quantity'] ?? null;
        $price = $row['price'] ?? null;
        $vat = $row['vat'] ?? null;

        if (empty($product_code) || empty($product)) {
            return;
        }

        $item = ItemCode::query()->where([
            ['company_id', $this->company_id],
            ['year', $this->year],
            ['product_code', $product_code],
        ])->first();

        if (empty($item)) {
            // Add data to batch array
            $this->batchData[] = [
                'company_id' => $this->company_id,
                'year' => $this->year,
                'product_code' => $product_code,
                'product' => $product,
                'unit' => $unit,
                'price' => $price,
                'created_by' => $this->user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        } else {
            $item->product = $product;
            $item->save();
        }

        // Insert batch data if it reaches 1000 records
        if (count($this->batchData) >= 1000) {
            ItemCode::insert($this->batchData);
            $this->batchData = []; // Reset batch data
        }

        $this->imported++;
    }

    protected function finalizeImport()
    {
        // Insert any remaining batch data
        if (!empty($this->batchData)) {
            ItemCode::insert($this->batchData);
        }
    }
}
