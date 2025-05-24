<?php

namespace App\Exports;

use App\Helpers\Enums\CellColors;
use App\Models\LotPlan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

final class DTechItemCodeExport implements FromCollection, WithEvents
{
    private int $rowIndex;
    private int $rowNumber = 0;
    private Collection $collection;

    public function __construct(Collection $collection)
    {
        $this->rowIndex = 1;
        $this->collection = $collection;
    }

    public function collection(): Collection
    {
        return $this->collection;
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

        $sheet->setCellValue("A$rowIndex", "FILE MÃ HÀNG HÓA");
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
        $records = $this->collection();
        foreach ($records as $index => $record) {
            $rowIndex = $this->increaseIndex();
            $this->setBorders($sheet, "A$rowIndex:U$rowIndex");
            $sheet->getStyle("E$rowIndex:I$rowIndex")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->setCellValue("A$rowIndex", $index + 1);
            // $sheet->setCellValue("B$rowIndex", $record['product_code']);
            // $sheet->setCellValue("C$rowIndex", $record['product_exchange']);
            // $sheet->setCellValue("D$rowIndex", $record['unit']);
            // $sheet->setCellValue("E$rowIndex", $record['opening_balance']);
            // $sheet->setCellValue("F$rowIndex", $record['purchase']);
            // $sheet->setCellValue("G$rowIndex", $record['sold']);
            // $sheet->setCellValue("H$rowIndex", $record['cost_price_sold']);
            // $sheet->setCellValue("I$rowIndex", "=E$rowIndex+F$rowIndex-H$rowIndex");
            // $sheet->setCellValue("J$rowIndex", "");
        }
    }
}
