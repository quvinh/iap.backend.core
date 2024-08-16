<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\ItemCode;
use Exception;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ImportedGoodsImport implements ToCollection, WithHeadingRow, WithStartRow
{
    protected $batchData = [];
    protected int $imported = 0;
    private int $user_id;
    private int $company_id;
    private int $year;
    private $cacheKey;

    public function __construct(int $company_id, int $year, int $user_id)
    {
        $this->company_id = $company_id;
        $this->year = $year;
        $this->user_id = $user_id;
        // $this->user_id = auth()->user()->getAuthIdentifier();
        $this->cacheKey = "company_{$this->company_id}_year_{$this->year}_product_codes";
        $this->initializeCache();
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

        // Insert any remaining batch data after the loop
        $this->finalizeImport();

        if ($this->imported == 0) {
            // throw new Exception('Không có bản ghi nào được thêm');
            Log::info('Không có bản ghi nào được thêm');
        }
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
        $quantity = $row['quantity'] ?? null;
        $price = $row['price'] ?? null;
        $currency = $row['currency'] ?? null;
        $currency_price = $row['currency_price'] ?? null;
        $isf_price = $row['isf_price'] ?? null;
        $isf_currency = $row['isf_currency'] ?? null;
        $isf_currency_price = $row['isf_currency_price'] ?? null;
        $import_tax = $row['import_tax'] ?? null;
        $special_consumption_tax = $row['special_consumption_tax'] ?? null;
        $vat = $row['vat'] ?? null;

        if (empty($product)) {
            return;
        }

        $product_code = $this->findProductCodeBySimilarity($product, 50);
        
        $this->imported++;
        Log::debug("{$this->imported}: {$product} 👉 {$product_code}");
    }

    protected function finalizeImport()
    {
        // Insert any remaining batch data
        if (!empty($this->batchData)) {
            ItemCode::insert($this->batchData);
        }

        // Clear cache
        // Cache::forget($this->cacheKey);
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
                $similarProductCode = $item['product_code'];
                // return $similarProductCode;
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
            ->get(['product_code', 'product'])
            ->toArray();

        Cache::put($this->cacheKey, $allProductCodes, now()->addMinutes(30));
    }
}
