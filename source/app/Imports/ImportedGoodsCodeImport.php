<?php
namespace App\Imports;

use App\Models\Department;
use Exception;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ImportedGoodsCodeImport implements ToCollection, WithHeadingRow, WithStartRow
{
    protected int $imported = 0;

    public function headingRow(): int
    {
        return 1;
    }

    public function startRow(): int
    {
        return 3;
    }
    public function collection(Collection $collection)
    {
        foreach ($collection as $row) {
            $this->importRow($row->toArray());
        }

        if ($this->imported == 0) throw new Exception('Không có bản ghi nào được thêm');
    }

    protected function importRow(array $row)
    {
        $product_code = $row['product_code'] ?? null;
        $product = $row['product'] ?? null;
        $unit = $row['unit'] ?? null;
        $quantity = $row['quantity'] ?? null;
        $total = $row['total'] ?? null;
        if (empty($product_code) || empty($product)) return;

        $this->imported++;
    }
}
