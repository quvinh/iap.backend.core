<?php

namespace App\Exports;

use App\Helpers\Enums\CellColors;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ReportSoldExport implements WithEvents
{
    private int $rowIndex;
    private mixed $record;

    public function __construct(mixed $record)
    {
        $this->rowIndex = 1;
        $this->record = $record;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
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
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(10);
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(10);
        $sheet->getColumnDimension('F')->setWidth(10);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(20);
        $sheet->getColumnDimension('I')->setWidth(20);
        $sheet->getColumnDimension('J')->setWidth(20);
        $sheet->getColumnDimension('K')->setWidth(30);
        $sheet->getColumnDimension('L')->setWidth(20);
        $sheet->getColumnDimension('M')->setWidth(20);
        $sheet->getColumnDimension('N')->setWidth(20);
        $sheet->getColumnDimension('O')->setWidth(20);
        $sheet->getColumnDimension('P')->setWidth(20);
        $sheet->getColumnDimension('Q')->setWidth(20);
        $sheet->getColumnDimension('R')->setWidth(20);
        $sheet->getColumnDimension('S')->setWidth(20);
        $sheet->getColumnDimension('T')->setWidth(20);
        $sheet->getColumnDimension('U')->setWidth(20);
        $sheet->getColumnDimension('V')->setWidth(20);
        $sheet->getColumnDimension('W')->setWidth(20);
        $sheet->getColumnDimension('X')->setWidth(20);
        $sheet->getColumnDimension('Y')->setWidth(20);
        $sheet->getColumnDimension('Z')->setWidth(20);
        $sheet->getColumnDimension('AA')->setWidth(10);
        $sheet->getColumnDimension('AB')->setWidth(10);
        $sheet->getColumnDimension('AC')->setWidth(10);
        $sheet->getColumnDimension('AD')->setWidth(10);
        $sheet->getColumnDimension('AE')->setWidth(10);
        $sheet->getColumnDimension('AF')->setWidth(10);
        $sheet->getColumnDimension('AG')->setWidth(20);
        $sheet->getColumnDimension('AH')->setWidth(20);
        $sheet->getColumnDimension('AI')->setWidth(20);
        $sheet->getColumnDimension('AJ')->setWidth(20);
        $sheet->getColumnDimension('AK')->setWidth(30);
        $sheet->getColumnDimension('AL')->setWidth(20);
        $sheet->getColumnDimension('AM')->setWidth(20);
        $sheet->getColumnDimension('AN')->setWidth(20);
        $sheet->getColumnDimension('AO')->setWidth(20);
        $sheet->getColumnDimension('AP')->setWidth(20);
        $sheet->getColumnDimension('AQ')->setWidth(20);
        $sheet->getColumnDimension('AR')->setWidth(20);
        $sheet->getColumnDimension('AS')->setWidth(20);
        $sheet->getColumnDimension('AT')->setWidth(20);
        $sheet->getColumnDimension('AU')->setWidth(20);
        $sheet->getColumnDimension('AV')->setWidth(20);
        $sheet->getColumnDimension('AW')->setWidth(20);
        $sheet->getColumnDimension('AX')->setWidth(20);
        $sheet->getColumnDimension('AY')->setWidth(20);
        $sheet->getColumnDimension('AZ')->setWidth(20);
        $sheet->getColumnDimension('BA')->setWidth(10);
        $sheet->getColumnDimension('BB')->setWidth(20);
    }

    /**
     * Set header excel
     * @param Sheet $sheet
     */
    function mainHeader(Sheet $sheet): void
    {
        $rowIndex = $this->rowIndex;
        $sheet->setCellValue("A$rowIndex", "Hiển thị trên sổ");
        $sheet->setCellValue("B$rowIndex", "Hình thức bán hàng");
        $sheet->setCellValue("C$rowIndex", "Phương thức thanh toán");
        $sheet->setCellValue("D$rowIndex", "Kiêm phiếu xuất kho");
        $sheet->setCellValue("E$rowIndex", "Lập kèm hoá đơn");
        $sheet->setCellValue("F$rowIndex", "Đã lập hoá đơn");
        $sheet->setCellValue("G$rowIndex", "Ngày hạch toán (*)");
        $sheet->setCellValue("H$rowIndex", "Ngày chứng từ (*)");
        $sheet->setCellValue("I$rowIndex", "Số chứng từ (*)");
        $sheet->setCellValue("J$rowIndex", "Số phiếu xuất");
        $sheet->setCellValue("K$rowIndex", "Lý do xuất");
        $sheet->setCellValue("L$rowIndex", "Mẫu số HĐ");
        $sheet->setCellValue("M$rowIndex", "Ký hiệu HĐ");
        $sheet->setCellValue("N$rowIndex", "Số hoá đơn");
        $sheet->setCellValue("O$rowIndex", "Ngày hoá đơn");
        $sheet->setCellValue("P$rowIndex", "Mã khách hàng");
        $sheet->setCellValue("Q$rowIndex", "Tên khách hàng");
        $sheet->setCellValue("R$rowIndex", "Địa chỉ");
        $sheet->setCellValue("S$rowIndex", "Mã số thuế");
        $sheet->setCellValue("T$rowIndex", "Diễn giải");
        $sheet->setCellValue("U$rowIndex", "Nộp vào TK");
        $sheet->setCellValue("V$rowIndex", "NV bán hàng");
        $sheet->setCellValue("W$rowIndex", "Loại tiền");
        $sheet->setCellValue("X$rowIndex", "Tỷ giá");
        $sheet->setCellValue("Y$rowIndex", "Mã hàng (*)");
        $sheet->setCellValue("Z$rowIndex", "Tên hàng");
        $sheet->setCellValue("AA$rowIndex", "Hàng khuyến mại");
        $sheet->setCellValue("AB$rowIndex", "TK Tiền/Chi phí/Nợ (*)");
        $sheet->setCellValue("AC$rowIndex", "TK Doanh thu/Có (*)");
        $sheet->setCellValue("AD$rowIndex", "ĐVT");
        $sheet->setCellValue("AE$rowIndex", "Số lượng");
        $sheet->setCellValue("AF$rowIndex", "Đơn giá sau thuế");
        $sheet->setCellValue("AG$rowIndex", "Đơn giá");
        $sheet->setCellValue("AH$rowIndex", "Thành tiền");
        $sheet->setCellValue("AI$rowIndex", "Thành tiền quy đổi");
        $sheet->setCellValue("AJ$rowIndex", "Tỷ lệ CK (%)");
        $sheet->setCellValue("AK$rowIndex", "Tiền chiết khấu");
        $sheet->setCellValue("AL$rowIndex", "Tiền chiết khấu quy đổi");
        $sheet->setCellValue("AM$rowIndex", "TK chiết khấu");
        $sheet->setCellValue("AN$rowIndex", "Giá tính thuế XK");
        $sheet->setCellValue("AO$rowIndex", "% thuế XK");
        $sheet->setCellValue("AP$rowIndex", "Tiền thuế XK");
        $sheet->setCellValue("AQ$rowIndex", "TK thuế XK");
        $sheet->setCellValue("AR$rowIndex", "% thuế GTGT");
        $sheet->setCellValue("AS$rowIndex", "Tiền thuế GTGT");
        $sheet->setCellValue("AT$rowIndex", "Tiền thuế GTGT quy đổi");
        $sheet->setCellValue("AU$rowIndex", "TK thuế GTGT");
        $sheet->setCellValue("AV$rowIndex", "HH không TH trên tờ khai thuế GTGT");
        $sheet->setCellValue("AW$rowIndex", "Kho");
        $sheet->setCellValue("AX$rowIndex", "TK giá vốn");
        $sheet->setCellValue("AY$rowIndex", "TK kho");
        $sheet->setCellValue("AZ$rowIndex", "Đơn giá vốn");
        $sheet->setCellValue("BA$rowIndex", "Tiền vốn");
        $sheet->setCellValue("BB$rowIndex", "Hàng hoá giữ hộ/bán hộ");
        $sheet->setCellValue("BC$rowIndex", "");
        $sheet->getStyle("A$rowIndex:BB$rowIndex")->getFont()->setBold(true);
        $this->setBackgroundColor($sheet, "A$rowIndex:BB$rowIndex", CellColors::YELLOW);
        $this->setBorders($sheet, "A$rowIndex:BB$rowIndex");
    }

    /**
     * Content table
     * @param Sheet $sheet
     */
    function content(Sheet $sheet): void
    {
        foreach ($this->record as $record) {
            $invoice_date = Carbon::parse($record->date)->format('d/m/Y');
            $company = $record->company()->first();
            $company_name = $company->name ?? "";
            $company_tax_code = $company->tax_code ?? "";
            $details = $record->invoice_details()->get();

            # Invoice detail
            foreach ($details as $detail) {
                if (!empty($detail->item_code->id)) {
                    $rowIndex = $this->increaseIndex();
                    // $sheet->setCellValue("A$rowIndex", "");
                    // $sheet->setCellValue("B$rowIndex", "");
                    // $sheet->setCellValue("C$rowIndex", "");
                    // $sheet->setCellValue("D$rowIndex", "");
                    // $sheet->setCellValue("E$rowIndex", "");
                    // $sheet->setCellValue("F$rowIndex", "");
                    $sheet->setCellValue("G$rowIndex", $invoice_date);
                    $sheet->setCellValue("H$rowIndex", $invoice_date);
                    $sheet->setCellValue("I$rowIndex", $record->invoice_number);
                    $sheet->setCellValue("J$rowIndex", "XK{$record->invoice_number}");
                    $sheet->setCellValue("K$rowIndex", "Xuất kho bán hàng cho {$record->partner_name} theo hoá đơn {$record->invoice_number}");
                    $sheet->setCellValue("L$rowIndex", $record->invoice_number_form);
                    $sheet->setCellValue("M$rowIndex", $record->invoice_symbol);
                    $sheet->setCellValue("N$rowIndex", $record->invoice_number);
                    $sheet->setCellValue("O$rowIndex", $invoice_date);
                    $sheet->setCellValue("P$rowIndex", $record->partner_tax_code);
                    $sheet->setCellValue("Q$rowIndex", $record->partner_name);
                    $sheet->setCellValue("R$rowIndex", $record->partner_address ?? "");
                    $sheet->setCellValue("S$rowIndex", $record->company_tax_code);
                    $sheet->setCellValue("T$rowIndex", $record->note ?? "Doanh thu bán hàng cho $company_name số {$record->invoice_number}");
                    // $sheet->setCellValue("U$rowIndex", "");
                    // $sheet->setCellValue("V$rowIndex", "");
                    // $sheet->setCellValue("W$rowIndex", "");
                    // $sheet->setCellValue("X$rowIndex", "");
                    $sheet->setCellValue("Y$rowIndex", $detail->item_code->product_code ?? "");
                    $sheet->setCellValue("Z$rowIndex", $detail->product ?? "");
                    // $sheet->setCellValue("AA$rowIndex", "");
                    $sheet->setCellValue("AB$rowIndex", "131");
                    // $sheet->setCellValue("AC$rowIndex", ""); // TK Doanh thu/Có (*)
                    $sheet->setCellValue("AD$rowIndex", $detail->unit);
                    $sheet->setCellValue("AE$rowIndex", $detail->quantity);
                    // $sheet->setCellValue("AF$rowIndex", "");
                    $sheet->setCellValue("AG$rowIndex", $detail->price);
                    $sheet->setCellValue("AH$rowIndex", $detail->total_money);
                    // $sheet->setCellValue("AI$rowIndex", "");
                    // $sheet->setCellValue("AJ$rowIndex", "");
                    // $sheet->setCellValue("AK$rowIndex", "");
                    // $sheet->setCellValue("AL$rowIndex", "");
                    // $sheet->setCellValue("AM$rowIndex", "");
                    // $sheet->setCellValue("AN$rowIndex", "");
                    // $sheet->setCellValue("AO$rowIndex", "");
                    // $sheet->setCellValue("AP$rowIndex", "");
                    // $sheet->setCellValue("AQ$rowIndex", "");
                    $sheet->setCellValue("AR$rowIndex", $detail->vat);
                    $sheet->setCellValue("AS$rowIndex", $detail->vat_money);
                    // $sheet->setCellValue("AT$rowIndex", "");
                    $sheet->setCellValue("AU$rowIndex", "33311");
                    // $sheet->setCellValue("AV$rowIndex", "");
                    // $sheet->setCellValue("AW$rowIndex", "");
                    // $sheet->setCellValue("AX$rowIndex", "");
                    // $sheet->setCellValue("AY$rowIndex", "");
                    // $sheet->setCellValue("AZ$rowIndex", "");
                    // $sheet->setCellValue("BA$rowIndex", "");
                    // $sheet->setCellValue("BB$rowIndex", "");
                    // $sheet->setCellValue("BC$rowIndex", "");
                    $this->setBorders($sheet, "A$rowIndex:BB$rowIndex");

                    # Format cells
                    $sheet->getStyle("G$rowIndex")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
                    $sheet->getStyle("H$rowIndex")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
                    $sheet->getStyle("O$rowIndex")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
                    $sheet->getStyle("AG$rowIndex")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                    $sheet->getStyle("AH$rowIndex")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                    $sheet->getStyle("AS$rowIndex")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                }
            }
        }
    }
}
