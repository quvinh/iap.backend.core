<?php

namespace App\Exports;

use App\Helpers\Enums\CellColors;
use App\Services\Company\ICompanyService;
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
    private ICompanyService $companyService;
    private mixed $companyId;
    private string $start;
    private string $end;

    public function __construct(ICompanyService $companyService, mixed $companyId, string $start, string $end)
    {
        $this->rowIndex = 1;
        $this->companyService = $companyService;
        $this->companyId = $companyId;
        $this->start = $start;
        $this->end = $end;
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

    /**
     * Content table
     * @param Sheet $sheet
     */
    function content(Sheet $sheet): void
    {
        $records = $this->companyService->inventory($this->companyId, $this->start, $this->end);
        foreach ($records as $index => $record) {
            $rowIndex = $this->increaseIndex();
            $this->setBorders($sheet, "A$rowIndex:J$rowIndex");
            $sheet->getStyle("E$rowIndex:I$rowIndex")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->setCellValue("A$rowIndex", $index + 1);
            $sheet->setCellValue("B$rowIndex", $record['product_code']);
            $sheet->setCellValue("C$rowIndex", $record['product_exchange']);
            $sheet->setCellValue("D$rowIndex", $record['unit']);
            $sheet->setCellValue("E$rowIndex", $record['opening_balance']);
            $sheet->setCellValue("F$rowIndex", $record['purchase']);
            $sheet->setCellValue("G$rowIndex", $record['sold']);
            $sheet->setCellValue("H$rowIndex", $record['cost_price_sold']);
            $sheet->setCellValue("I$rowIndex", "=E$rowIndex+F$rowIndex-H$rowIndex");
            $sheet->setCellValue("J$rowIndex", "");
        }
    }
}
