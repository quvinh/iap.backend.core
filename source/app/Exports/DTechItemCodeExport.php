<?php

namespace App\Exports;

use App\Helpers\Enums\CellColors;
use App\Models\Company;
use App\Models\InvoiceDetail;
use App\Models\ItemCode;
use App\Models\LotPlan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

final class DTechItemCodeExport implements WithEvents
{
    private int $rowIndex;
    private array $params;

    public function __construct(array $params)
    {
        $this->rowIndex = 1;
        $this->params = $params;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                // $sheet->getDelegate()->freezePane('A1');
                $this->setWidthColumns($sheet);
                $this->mainHeader($sheet);
                $this->content($sheet);
            }
        ];
    }

    function setBackgroundColor(Sheet $sheet, string $cells, string $color = CellColors::WHITE): void
    {
        $sheet->getStyle($cells)->applyFromArray([
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $color]],
        ]);
    }
    function setBorders(Sheet $sheet, string $cells, string $color = CellColors::BLACK): void
    {
        $sheet->getStyle($cells)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $color]]],
        ]);
    }
    function increaseIndex(int $increase = 1): int
    {
        $this->rowIndex += $increase;
        return $this->rowIndex;
    }

    /**
     * Set width columns
     * @param Sheet $sheet
     */
    function setWidthColumns(Sheet $sheet): void
    {
        $columns = ['B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U'];

        $sheet->getColumnDimension('A')->setWidth(7);
        foreach ($columns as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    /**
     * Set header excel
     * @param Sheet $sheet
     */
    function mainHeader(Sheet $sheet): void
    {
        $rowIndex = $this->rowIndex;
        $params = $this->params;
        $title = "";
        if (isset($params['company_id']) && isset($params['year'])) {
            $year = $params['year'];
            $company = Company::find($params['company_id']);
            if (!empty($company)) {
                $title = ": {$company->name} Năm {$year}";
            }
        }

        $sheet->setCellValue("A$rowIndex", "File Mã hàng hóa{$title}");
        $rowIndex = $this->increaseIndex();

        $sheet->setCellValue("A$rowIndex", "STT");
        $sheet->setCellValue("B$rowIndex", "Mã");
        $sheet->setCellValue("C$rowIndex", "Tên");
        $sheet->setCellValue("D$rowIndex", "ĐVT Chính");
        $sheet->setCellValue("E$rowIndex", "Tính chất");
        $sheet->setCellValue("F$rowIndex", "Ngành nghề");
        $sheet->setCellValue("G$rowIndex", "Giảm trừ thuế");
        $sheet->setCellValue("H$rowIndex", "Là Sản phẩm");
        $sheet->setCellValue("I$rowIndex", "Đơn giá bán");
        $sheet->setCellValue("J$rowIndex", "Nhóm TK");
        $sheet->setCellValue("K$rowIndex", "Mã vạch");
        $sheet->setCellValue("L$rowIndex", "SL tồn");
        $sheet->setCellValue("M$rowIndex", "Đơn giá vốn");
        $sheet->setCellValue("N$rowIndex", "Đơn vị phụ 1");
        $sheet->setCellValue("O$rowIndex", "Tỷ lệ quy đổi 1");
        $sheet->setCellValue("P$rowIndex", "Đơn vị phụ 2");
        $sheet->setCellValue("Q$rowIndex", "Tỷ lệ quy đổi 2");
        $sheet->setCellValue("R$rowIndex", "Đơn vị phụ 3");
        $sheet->setCellValue("S$rowIndex", "Tỷ lệ quy đổi 3");
        $sheet->setCellValue("T$rowIndex", "Đơn vị phụ 4");
        $sheet->setCellValue("U$rowIndex", "Tỷ lệ quy đổi 4");

        $sheet->getStyle("A$rowIndex:U$rowIndex")->getFont()->setBold(true);
        $this->setBackgroundColor($sheet, "A$rowIndex:U$rowIndex", CellColors::YELLOW);
        $this->setBorders($sheet, "A$rowIndex:U$rowIndex");
    }

    /**
     * Content table
     * @param Sheet $sheet
     */
    function content(Sheet $sheet): void
    {
        $sortType = 'desc';
        $query = ItemCode::query()->select(['id', 'product_code', 'product', 'price']);
        $params = $this->params;
        
        if (isset($params['sort'])) {
            $sort = $params['sort'];
            $sortType = $sort['type'] ?? 'desc';
        }

        if (isset($params['company_id'])) {
            $company_id = $params['company_id'];
            $query->where('company_id', '=', $company_id);
        }

        if (isset($params['year'])) {
            $year = $params['year'];
            $query->where('year', '=', $year);
        }

        if (isset($params['price_from'])) {
            $price_from = $params['price_from'];
            $query->where('price', '>=', $price_from);
        }

        if (isset($params['price_to'])) {
            $price_to = $params['price_to'];
            $query->where('price', '<=', $price_to);
        }

        $records = $query->orderBy('id', $sortType)->get();

        foreach ($records as $index => $record) {
            $rowIndex = $this->increaseIndex();
            $this->setBorders($sheet, "A$rowIndex:U$rowIndex");
            // $sheet->getStyle("E$rowIndex:I$rowIndex")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->setCellValue("A$rowIndex", $index + 1);
            $sheet->setCellValue("B$rowIndex", $record['product_code']);
            $sheet->setCellValue("C$rowIndex", $record['product']);
            $sheet->setCellValue("D$rowIndex", "Hàng hóa");
            $sheet->setCellValue("E$rowIndex", "Hoạt động bán buôn, bán lẻ các loại hàng hóa (trừ giá trị hàng hóa đại lý bán đúng giá hưởng hoa hồng)");
            $sheet->setCellValue("F$rowIndex", "");
            $sheet->setCellValue("G$rowIndex", "");
            $sheet->setCellValue("H$rowIndex", "");
            $sheet->setCellValue("I$rowIndex", "1523");
            $sheet->setCellValue("J$rowIndex", "");
            $sheet->setCellValue("K$rowIndex", "");
            $sheet->setCellValue("L$rowIndex", "");
            $sheet->setCellValue("M$rowIndex", "");
            $sheet->setCellValue("N$rowIndex", "");
            $sheet->setCellValue("O$rowIndex", "");
            $sheet->setCellValue("P$rowIndex", "");
            $sheet->setCellValue("Q$rowIndex", "");
            $sheet->setCellValue("R$rowIndex", "");
            $sheet->setCellValue("S$rowIndex", "");
            $sheet->setCellValue("T$rowIndex", "");
            $sheet->setCellValue("U$rowIndex", "");
        }
    }
}
