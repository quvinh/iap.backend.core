<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class DataAnnouncementExport implements WithEvents
{
    private const HEIGHT_TITLE = 30;
    private const ALL_BORDERS = ['allBorders' => ['borderStyle' => Border::BORDER_THIN]];

    public function __construct()
    {
    }

    public function registerEvents(): array
    {
        $rowIndex = 5;
        return [
            AfterSheet::class => function (AfterSheet $event) use ($rowIndex) {
                $sheet = $event->sheet;
                $this->setWidthColumns($sheet);
                $this->setTitle($sheet);
                $this->mainHeader($sheet, $rowIndex);

                # Data
                $rowIndex++;
                $this->doanhThu($sheet, $rowIndex);
            },
        ];
    }

    function setBackgroundColor(Sheet $sheet, string $cells, string $color = 'FFFFFF'): void
    {
        $sheet->getStyle($cells)->applyFromArray([
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $color]],
        ]);
    }
    function setBorders(Sheet $sheet, string $cells, string $color = '000000'): void
    {
        $sheet->getStyle($cells)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $color]]],
        ]);
    }
    
    /**
     * Set width columns
     * @param Sheet $sheet
     */
    function setWidthColumns(Sheet $sheet): void
    {
        $sheet->getColumnDimension('A')->setWidth(7);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(20);
    }

    /**
     * Set title excel
     * @param Sheet $sheet
     * @param int $rowIndex
     */
    function setTitle(Sheet $sheet, int $rowIndex = 1): void
    {
        $sheet->setCellValue("B$rowIndex", "THÔNG BÁO SỐ LIỆU" . PHP_EOL . "Từ 01/01/2023 đến 31/12/2023");
        $sheet->mergeCells("B$rowIndex:F$rowIndex");
        $sheet->getRowDimension($rowIndex)->setRowHeight(self::HEIGHT_TITLE);
        $sheet->getStyle("B$rowIndex:F$rowIndex")->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => ['bold' => true],
            'borders' => self::ALL_BORDERS,
        ]);
        $sheet->getStyle("B$rowIndex")->getAlignment()->setWrapText(true);

        # Company name
        $rowIndex++;
        $sheet->setCellValue("B$rowIndex", "Tên đơn vị");
        $sheet->setCellValue("C$rowIndex", "..."); # Fix here
        $sheet->mergeCells("C$rowIndex:F$rowIndex");
        $sheet->getStyle("B$rowIndex:F$rowIndex")->getFont()->setBold(true);
        $this->setBorders($sheet, "B$rowIndex:F$rowIndex");

        # Tax code
        $rowIndex++;
        $sheet->setCellValue("B$rowIndex", "Mã số thuế");
        $sheet->setCellValue("C$rowIndex", "..."); # Fix here
        $sheet->mergeCells("C$rowIndex:F$rowIndex");
        $sheet->getStyle("B$rowIndex:F$rowIndex")->getFont()->setBold(true);
        $this->setBorders($sheet, "B$rowIndex:F$rowIndex");
    }

    /**
     * Set title excel
     * @param Sheet $sheet
     * @param int $rowIndex
     */
    function mainHeader(Sheet $sheet, int $rowIndex = 1): void
    {
        $sheet->setCellValue("B$rowIndex", "Chỉ tiêu");
        $sheet->setCellValue("C$rowIndex", "Bán ra");
        $sheet->setCellValue("D$rowIndex", "Tồn đầu kỳ");
        $sheet->setCellValue("E$rowIndex", "Mua vào");
        $sheet->setCellValue("F$rowIndex", "Tồn cuối kỳ");
        $sheet->setCellValue("G$rowIndex", "Ghi chú");
        $sheet->getStyle("A$rowIndex:G$rowIndex")->getFont()->setBold(true);
        $this->setBackgroundColor($sheet, "A$rowIndex:G$rowIndex", "FFF2CC");
        $this->setBorders($sheet, "A$rowIndex:G$rowIndex");
    }

    /**
     * A. Doanh thu
     * @param Sheet $sheet
     * @param int $rowIndex
     */
    function doanhThu(Sheet $sheet, int $rowIndex = 1): void
    {
        $sheet->setCellValue("A$rowIndex", "A");
        $sheet->setCellValue("B$rowIndex", "Doanh thu");
        $sheet->getStyle("A$rowIndex:B$rowIndex")->getFont()->setBold(true);
        $this->setBackgroundColor($sheet, "A$rowIndex:B$rowIndex", "FFF2CC");
        $this->setBorders($sheet, "A$rowIndex:G$rowIndex");
    }
}
