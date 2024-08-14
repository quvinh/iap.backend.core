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

class ImportedGoodsCodeImport implements ToCollection, WithHeadingRow, WithStartRow
{
    protected $batchData = [];
    protected int $imported = 0;
    private int $user_id;
    private int $company_id;
    private int $year;
    private $cacheKey;

    public function __construct(int $company_id, int $year)
    {
        $this->company_id = $company_id;
        $this->year = $year;
        $this->user_id = auth()->user()->getAuthIdentifier();
        $this->cacheKey = "company_{$this->company_id}_year_{$this->year}_product_codes";
        $this->initializeCache();
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
            Cache::clear($this->cacheKey);
            throw new Exception('Không có bản ghi nào được thêm');
        }
    }

    protected function importRow(array $row)
    {
        $product_code = $row['product_code'] ?? null;
        $product = $row['product'] ?? null;
        $unit = $row['unit'] ?? '/';
        $quantity = floatval($row['quantity'] ?? 0);
        $total = floatval($row['total'] ?? 0);

        if (empty($product_code) || empty($product)) {
            return;
        }

        // Check if record already exists
        // $recordExists = ItemCode::query()->where([
        //     ['company_id', $this->company_id],
        //     ['year', $this->year],
        //     ['product_code', $product_code],
        // ])->exists();

        // Check if record already exists
        $recordExists = collect(Cache::get($this->cacheKey, []))->contains(function ($item) use ($product_code) {
            return $item['product_code'] === $product_code;
        });

        if ($recordExists) {
            return;
        }

        $price = $quantity > 0 ? round($total / $quantity, 2) : 0;

        // Add data to batch array
        $this->batchData[] = [
            'company_id' => $this->company_id,
            'year' => $this->year,
            'product_code' => $product_code,
            'product' => $product,
            'unit' => $unit,
            'quantity' => $quantity,
            'price' => $price,
            'opening_balance_value' => $total,
            'is_imported_goods' => 1,
            'created_by' => $this->user_id,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Update cache with the new item code
        $updatedCache = array_merge(Cache::get($this->cacheKey, []), [
            ['product_code' => $product_code, 'product' => $product]
        ]);
        Cache::put($this->cacheKey, $updatedCache, now()->addMinutes(30));

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

        // Clear cache
        // Cache::forget($this->cacheKey);
    }

    protected function initializeCache()
    {
        $allProductCodes = ItemCode::where('company_id', $this->company_id)
            ->where('year', $this->year)
            ->get(['product_code', 'product'])
            ->toArray();

        Cache::put($this->cacheKey, $allProductCodes, now()->addMinutes(30));
    }
}
