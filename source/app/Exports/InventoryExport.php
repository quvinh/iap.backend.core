<?php

namespace App\Exports;

use App\Helpers\Enums\CellColors;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class InventoryExport implements WithEvents
{
    private int $rowIndex;

    public function __construct()
    {
        $this->rowIndex = 1;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $this->setWidthColumns($sheet);
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
        $sheet->getColumnDimension('A')->setWidth(7);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(20);
        $sheet->getColumnDimension('I')->setWidth(20);
        $sheet->getColumnDimension('J')->setWidth(20);
    }

    /**
     * Set header excel
     * @param Sheet $sheet
     */
    function mainHeader(Sheet $sheet): void
    {
        $rowIndex = $this->rowIndex;
        $sheet->setCellValue("A$rowIndex", "STT");
        $sheet->setCellValue("B$rowIndex", "Mã hàng");
        $sheet->setCellValue("C$rowIndex", "Tên HH(quy đổi)");
        $sheet->setCellValue("D$rowIndex", "ĐVT");
        $sheet->setCellValue("E$rowIndex", "Đầu kỳ");
        $sheet->setCellValue("F$rowIndex", "Mua vào");
        $sheet->setCellValue("G$rowIndex", "Bán ra");
        $sheet->setCellValue("H$rowIndex", "Giá vốn");
        $sheet->setCellValue("I$rowIndex", "Cuối kỳ");
        $sheet->setCellValue("J$rowIndex", "Ghi chú");
        $sheet->getStyle("A$rowIndex:J$rowIndex")->getFont()->setBold(true);
        $this->setBackgroundColor($sheet, "A$rowIndex:J$rowIndex", CellColors::YELLOW);
        $this->setBorders($sheet, "A$rowIndex:J$rowIndex");
    }
}
