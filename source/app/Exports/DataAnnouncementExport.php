<?php

namespace App\Exports;

use App\Helpers\Enums\CellColors;
use App\Models\Company;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class DataAnnouncementExport implements WithEvents
{
    private const HEIGHT_TITLE = 30;
    private const ALL_BORDERS = ['allBorders' => ['borderStyle' => Border::BORDER_THIN]];
    private int $rowIndex;
    private $company;
    private $record;
    private $formulaTotalDataA;

    public function __construct(Company $company, mixed $record)
    {
        $this->rowIndex = 5;
        $this->company = $company;
        $this->record = $record;
        $this->formulaTotalDataA = (object) [
            'sold' => null,
            'opening_balance' => null,
            'purchase' => null,
            'ending_balace' => null,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $this->setWidthColumns($sheet);
                $this->setTitle($sheet);
                $this->mainHeader($sheet);

                # A.Doanh thu
                $this->doanhThu($sheet);

                # B.Chi phi
                $this->chiPhi($sheet);

                # C.Thue GTGT
                $this->thueGTGT($sheet);

                # D.Loi nhuan
                $this->loiNhuan($sheet);
            },
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
        $sheet->setCellValue("C$rowIndex", $this->company->name ?? "...");
        $sheet->mergeCells("C$rowIndex:F$rowIndex");
        $sheet->getStyle("B$rowIndex:F$rowIndex")->getFont()->setBold(true);
        $this->setBorders($sheet, "B$rowIndex:F$rowIndex");

        # Tax code
        $rowIndex++;
        $sheet->setCellValue("B$rowIndex", "Mã số thuế");
        $sheet->setCellValue("C$rowIndex", $this->company->tax_code ?? "...");
        $sheet->mergeCells("C$rowIndex:F$rowIndex");
        $sheet->getStyle("B$rowIndex:F$rowIndex")->getFont()->setBold(true);
        $this->setBorders($sheet, "B$rowIndex:F$rowIndex");
    }

    /**
     * Set title excel
     * @param Sheet $sheet
     */
    function mainHeader(Sheet $sheet): void
    {
        $rowIndex = $this->increaseIndex();
        $sheet->setCellValue("B$rowIndex", "Chỉ tiêu");
        $sheet->setCellValue("C$rowIndex", "Bán ra");
        $sheet->setCellValue("D$rowIndex", "Tồn đầu kỳ");
        $sheet->setCellValue("E$rowIndex", "Mua vào");
        $sheet->setCellValue("F$rowIndex", "Tồn cuối kỳ");
        $sheet->setCellValue("G$rowIndex", "Ghi chú");
        $sheet->getStyle("A$rowIndex:G$rowIndex")->getFont()->setBold(true);
        $this->setBackgroundColor($sheet, "A$rowIndex:G$rowIndex", CellColors::YELLOW);
        $this->setBorders($sheet, "A$rowIndex:G$rowIndex");
    }

    /**
     * A. Doanh thu
     * @param Sheet $sheet
     */
    function doanhThu(Sheet $sheet): void
    {
        $rowIndex = $this->increaseIndex();
        $sheet->setCellValue("A$rowIndex", "A");
        $sheet->setCellValue("B$rowIndex", "Doanh thu");
        $sheet->getStyle("A$rowIndex:B$rowIndex")->getFont()->setBold(true);
        $this->setBackgroundColor($sheet, "A$rowIndex:B$rowIndex", CellColors::BLUE);
        $this->setBorders($sheet, "A$rowIndex:G$rowIndex");

        # I.HH-DV Chinh
        $this->hangHoaDichVuChinh($sheet);

        # II.Doanh thu khac
        $this->doanhThuKhac($sheet);

        # III.Doanh thu tai chinh
        $this->doanhThuTaiChinh($sheet);
    }

    /**
     * I. Hang hoa - Dich vu chinh
     * @param Sheet $sheet
     */
    function hangHoaDichVuChinh(Sheet $sheet): void
    {
        $rowIndex = $this->increaseIndex();
        $sheet->setCellValue("A$rowIndex", "I");
        $sheet->setCellValue("B$rowIndex", "HH-DV Chính");
        $sheet->getStyle("A$rowIndex:B$rowIndex")->getFont()->setBold(true);
        $this->setBackgroundColor($sheet, "A$rowIndex:B$rowIndex", CellColors::GRAY);
        $this->setBorders($sheet, "A$rowIndex:G$rowIndex");

        # Get data-analysis
        $record = (object) $this->record;
        $data = $record->data_analysis ?? [];
        $indexStart = $indexEnd = 0;
        foreach ($data as $index => $row) {
            $rowIndex = $this->increaseIndex();
            $row = (object) $row;
            if ($index == 0) $indexStart = $rowIndex;
            $indexEnd = $rowIndex;

            $this->setBorders($sheet, "A$rowIndex:G$rowIndex");
            $sheet->getStyle("C$rowIndex:F$rowIndex")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

            $sheet->setCellValue("A$rowIndex", $index + 1);
            $sheet->setCellValue("B$rowIndex", $row->formula_name);
            $sheet->setCellValue("C$rowIndex", $row->sold);
            $sheet->setCellValue("D$rowIndex", $row->opening_balance);
            $sheet->setCellValue("E$rowIndex", $row->purchase);
            $sheet->setCellValue("F$rowIndex", "=SUM(D$rowIndex:E$rowIndex)");
        }

        # Sum
        $rowIndex = $this->increaseIndex();
        if ($indexStart != 0 && $indexEnd != 0) {
            $this->setBorders($sheet, "A$rowIndex:G$rowIndex");
            $sheet->getStyle("C$rowIndex:F$rowIndex")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->getStyle("A$rowIndex:G$rowIndex")->getFont()->setBold(true);
            $this->setBackgroundColor($sheet, "A$rowIndex:G$rowIndex", CellColors::GRAY);
            $sheet->mergeCells("A$rowIndex:B$rowIndex");

            $sheet->setCellValue("A$rowIndex", "Cộng");
            $sheet->setCellValue("C$rowIndex", "=SUM(C$indexStart:C$indexEnd)");
            $sheet->setCellValue("D$rowIndex", "=SUM(D$indexStart:D$indexEnd)");
            $sheet->setCellValue("E$rowIndex", "=SUM(E$indexStart:E$indexEnd)");
            $sheet->setCellValue("F$rowIndex", "=SUM(D$rowIndex:E$rowIndex)");
            $sheet->setCellValue("G$rowIndex", "/");

            # Set formula total
            $this->formulaTotalDataA->sold = "=C$rowIndex";
            $this->formulaTotalDataA->opening_balance = "=D$rowIndex";
            $this->formulaTotalDataA->purchase = "=E$rowIndex";
            $this->formulaTotalDataA->ending_balance = "=F$rowIndex";
        }
    }

    /**
     * II. Doanh thu khac
     * @param Sheet $sheet
     */
    function doanhThuKhac(Sheet $sheet): void
    {
        $rowIndex = $this->increaseIndex();

        $sheet->setCellValue("A$rowIndex", "II");
        $sheet->setCellValue("B$rowIndex", "Doanh thu khác");
        $sheet->getStyle("A$rowIndex:B$rowIndex")->getFont()->setBold(true);
        $this->setBackgroundColor($sheet, "A$rowIndex:B$rowIndex", CellColors::GRAY);
        $this->setBorders($sheet, "A$rowIndex:G$rowIndex");

        $rowIndex = $this->increaseIndex();
        $this->setBorders($sheet, "A$rowIndex:G$rowIndex");

        # Code here

        $rowIndex = $this->increaseIndex();
        $this->setBorders($sheet, "A$rowIndex:G$rowIndex");
        $this->setBackgroundColor($sheet, "A$rowIndex:G$rowIndex", CellColors::GRAY);
        $sheet->mergeCells("A$rowIndex:B$rowIndex");
        $sheet->getStyle("A$rowIndex:G$rowIndex")->getFont()->setBold(true);
        $sheet->getStyle("C$rowIndex:F$rowIndex")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $sheet->setCellValue("A$rowIndex", "Cộng");
        $sheet->setCellValue("C$rowIndex", 0);
        $sheet->setCellValue("G$rowIndex", "/");
        if (isset($this->formulaTotalDataA->sold)) {
            $this->formulaTotalDataA->sold .= "+C$rowIndex";
        }
    }

    /**
     * III. Doanh thu tai chinh
     * @param Sheet $sheet
     */
    function doanhThuTaiChinh(Sheet $sheet): void
    {
        $this->rowIndex += 1;
        $rowIndex = $this->rowIndex;
        $sheet->setCellValue("A$rowIndex", "III");
        $sheet->setCellValue("B$rowIndex", "Doanh thu tài chính");
        $sheet->getStyle("A$rowIndex:B$rowIndex")->getFont()->setBold(true);
        $sheet->getStyle("C$rowIndex:F$rowIndex")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $this->setBackgroundColor($sheet, "A$rowIndex:B$rowIndex", CellColors::GRAY);
        $this->setBorders($sheet, "A$rowIndex:G$rowIndex");

        # Code here

        $rowIndex = $this->increaseIndex();
        $this->setBorders($sheet, "A$rowIndex:G$rowIndex");
        $rowIndex = $this->increaseIndex();
        $this->setBorders($sheet, "A$rowIndex:G$rowIndex");
        $this->setBackgroundColor($sheet, "A$rowIndex:G$rowIndex", CellColors::GRAY);
        $sheet->mergeCells("A$rowIndex:B$rowIndex");
        $sheet->getStyle("A$rowIndex:G$rowIndex")->getFont()->setBold(true);
        $sheet->getStyle("C$rowIndex:F$rowIndex")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $sheet->setCellValue("A$rowIndex", "Cộng");
        $sheet->setCellValue("C$rowIndex", 0);
        $sheet->setCellValue("G$rowIndex", "/");
        if (isset($this->formulaTotalDataA->sold)) {
            $this->formulaTotalDataA->sold .= "+C$rowIndex";
        }

        # Calculate total money (I+II+II)
        $rowIndex = $this->increaseIndex();
        $sheet->getStyle("A$rowIndex:G$rowIndex")->getFont()->setBold(true);
        $this->setBackgroundColor($sheet, "A$rowIndex:G$rowIndex", CellColors::GRAY);
        $sheet->getStyle("C$rowIndex:F$rowIndex")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $this->setBorders($sheet, "A$rowIndex:G$rowIndex");
        $sheet->setCellValue("A$rowIndex", "Tổng cộng (I+II+III)");
        $sheet->setCellValue("C$rowIndex", $this->formulaTotalDataA->sold ?? "");
        $sheet->setCellValue("D$rowIndex", $this->formulaTotalDataA->opening_balance ?? "");
        $sheet->setCellValue("E$rowIndex", $this->formulaTotalDataA->purchase ?? "");
        $sheet->setCellValue("F$rowIndex", $this->formulaTotalDataA->ending_balance ?? "");
    }

    /**
     * B. Chi phi
     * @param Sheet $sheet
     */
    function chiPhi(Sheet $sheet): void
    {
        $rowIndex = $this->increaseIndex(2);
        $sheet->setCellValue("A$rowIndex", "B");
        $sheet->setCellValue("B$rowIndex", "Chi phí");
        $sheet->setCellValue("C$rowIndex", "CP có hoá đơn");
        $sheet->setCellValue("D$rowIndex", "CP kết chuyển");
        $sheet->setCellValue("E$rowIndex", "CP trên CT khác");
        $sheet->setCellValue("F$rowIndex", "Cộng");
        $sheet->setCellValue("G$rowIndex", "Đề xuất");
        $sheet->setCellValue("H$rowIndex", "Lý do");
        $sheet->getStyle("A$rowIndex:B$rowIndex")->getFont()->setBold(true);
        $this->setBackgroundColor($sheet, "A$rowIndex:B$rowIndex", CellColors::BLUE);
        $this->setBackgroundColor($sheet, "C$rowIndex:H$rowIndex", CellColors::GRAY);
        $this->setBorders($sheet, "A$rowIndex:H$rowIndex");

        # I.Cac CP SXKD
        $this->cacCPSXKD($sheet);

        # II.Cac CP Khac
        $this->cacCPKhac($sheet);
    }

    /**
     * I. Cac CP SXKD
     * @param Sheet $sheet
     */
    function cacCPSXKD(Sheet $sheet): void
    {
        $rowIndex = $this->increaseIndex();
        $sheet->setCellValue("A$rowIndex", "I");
        $sheet->setCellValue("B$rowIndex", "Các CP SXKD");
        $sheet->getStyle("A$rowIndex:B$rowIndex")->getFont()->setBold(true);
        $this->setBackgroundColor($sheet, "A$rowIndex:B$rowIndex", CellColors::GRAY);
        $this->setBorders($sheet, "A$rowIndex:G$rowIndex");

        # Code here
    }

    /**
     * II. Cac CP Khac
     * @param Sheet $sheet
     */
    function cacCPKhac(Sheet $sheet): void
    {
        $rowIndex = $this->increaseIndex();
        $sheet->setCellValue("A$rowIndex", "I");
        $sheet->setCellValue("B$rowIndex", "Các CP Khác");
        $sheet->getStyle("A$rowIndex:B$rowIndex")->getFont()->setBold(true);
        $this->setBackgroundColor($sheet, "A$rowIndex:B$rowIndex", CellColors::GRAY);
        $this->setBorders($sheet, "A$rowIndex:G$rowIndex");

        # Code here
    }

    /**
     * C. Thue GTGT
     * @param Sheet $sheet
     */
    function thueGTGT(Sheet $sheet): void
    {
        # Get data-vat
        $record = (object) $this->record;
        $data = (object) $record->data_vat_money ?? (object) [];

        $rowIndex = $this->increaseIndex(2);
        $sheet->setCellValue("A$rowIndex", "C");
        $sheet->setCellValue("B$rowIndex", "Thuế GTGT");
        $sheet->setCellValue("C$rowIndex", "Dư đầu kỳ");
        $sheet->setCellValue("D$rowIndex", "Mua vào");
        $sheet->setCellValue("E$rowIndex", "Bán ra");
        $sheet->setCellValue("F$rowIndex", "Dư cuối kỳ");
        $sheet->setCellValue("G$rowIndex", "Đã nộp");
        $sheet->setCellValue("H$rowIndex", "Lý do");
        $sheet->getStyle("A$rowIndex:B$rowIndex")->getFont()->setBold(true);
        $this->setBackgroundColor($sheet, "A$rowIndex:B$rowIndex", CellColors::BLUE);
        $this->setBackgroundColor($sheet, "C$rowIndex:H$rowIndex", CellColors::GRAY);
        $this->setBorders($sheet, "A$rowIndex:H$rowIndex");

        $rowIndex = $this->increaseIndex();
        $sheet->getStyle("C$rowIndex:G$rowIndex")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $this->setBorders($sheet, "A$rowIndex:H$rowIndex");
        $sheet->setCellValue("C$rowIndex", $data->opening_balance_vat ?? 0);
        $sheet->setCellValue("D$rowIndex", $data->purchase ?? 0);
        $sheet->setCellValue("E$rowIndex", $data->sold ?? 0);
        $sheet->setCellValue("F$rowIndex", "=C$rowIndex+E$rowIndex-D$rowIndex");
    }

    /**
     * D. Loi nhuan
     * @param Sheet $sheet
     */
    function loiNhuan(Sheet $sheet): void
    {
        $rowIndex = $this->increaseIndex(2);
        $sheet->setCellValue("A$rowIndex", "D");
        $sheet->setCellValue("B$rowIndex", "Lợi nhuận");
        $sheet->setCellValue("C$rowIndex", "% Doanh thu");
        $sheet->setCellValue("D$rowIndex", "~ Số tiền");
        $sheet->getStyle("A$rowIndex:B$rowIndex")->getFont()->setBold(true);
        $this->setBackgroundColor($sheet, "A$rowIndex:B$rowIndex", CellColors::BLUE);
        $this->setBackgroundColor($sheet, "C$rowIndex:D$rowIndex", CellColors::GRAY);
        $this->setBorders($sheet, "A$rowIndex:D$rowIndex");

        $rowIndex = $this->increaseIndex();
        $sheet->getStyle("D$rowIndex")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $this->setBorders($sheet, "A$rowIndex:D$rowIndex");
        $sheet->setCellValue("A$rowIndex", "421");
        $sheet->setCellValue("B$rowIndex", "Lợi nhuận trước thuế");
        $sheet->setCellValue("C$rowIndex", "");
        $sheet->setCellValue("D$rowIndex", 0);

        $rowIndex = $this->increaseIndex();
        $sheet->getStyle("D$rowIndex")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $this->setBorders($sheet, "A$rowIndex:D$rowIndex");
        $sheet->setCellValue("A$rowIndex", "421");
        $sheet->setCellValue("B$rowIndex", "Lợi nhuận sau thuế");
        $sheet->setCellValue("C$rowIndex", "");
        $sheet->setCellValue("D$rowIndex", 0);

    }
}
