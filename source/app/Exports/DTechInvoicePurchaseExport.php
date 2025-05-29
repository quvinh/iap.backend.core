<?php

namespace App\Exports;

use App\Helpers\Enums\CellColors;
use App\Helpers\Enums\InvoiceTypes;
use App\Models\Company;
use App\Models\InvoiceDetail;
use App\Models\ItemCode;
use App\Models\LotPlan;
use Carbon\Carbon;
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

final class DTechInvoicePurchaseExport implements WithEvents
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
        $columns = [
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
            'Z',
            'AA',
            'AB',
            'AC',
            'AD'
        ];

        $sheet->getColumnDimension('A')->setWidth(15);
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
        if (isset($params['company_id']) && isset($params['start_date']) && isset($params['end_date'])) {
            $start = Carbon::parse($params['start_date'])->format('d/m/Y');
            $end = Carbon::parse($params['end_date'])->format('d/m/Y');
            $company = Company::find($params['company_id']);
            if (!empty($company)) {
                $title = ": {$company->name} Từ {$start} đến {$end}";
            }
        }

        $sheet->setCellValue("A$rowIndex", "Mua vào{$title}");
        $rowIndex = $this->increaseIndex();

        $sheet->setCellValue("A$rowIndex", "Ngày");
        $sheet->setCellValue("B$rowIndex", "Chứng từ");
        $sheet->setCellValue("C$rowIndex", "Seri hóa đơn");
        $sheet->setCellValue("D$rowIndex", "Số hóa đơn");
        $sheet->setCellValue("E$rowIndex", "Ngày hóa đơn");
        $sheet->setCellValue("F$rowIndex", "Ông/Bà");
        $sheet->setCellValue("G$rowIndex", "Diễn giải");
        $sheet->setCellValue("H$rowIndex", "Mã Đối tượng");
        $sheet->setCellValue("I$rowIndex", "Đối tượng");
        $sheet->setCellValue("J$rowIndex", "Kho");
        $sheet->setCellValue("K$rowIndex", "TK ngân hàng Nợ");
        $sheet->setCellValue("L$rowIndex", "Ngân hàng Nợ");
        $sheet->setCellValue("M$rowIndex", "C.Khấu H.đơn");
        $sheet->setCellValue("N$rowIndex", "Lý do (*)");
        $sheet->setCellValue("O$rowIndex", "Mã Vật tư");
        $sheet->setCellValue("P$rowIndex", "Tên Vật tư hàng hóa");
        $sheet->setCellValue("Q$rowIndex", "ĐVT Chính");
        $sheet->setCellValue("R$rowIndex", "Số lượng");
        $sheet->setCellValue("S$rowIndex", "Đơn giá");
        $sheet->setCellValue("T$rowIndex", "Số tiền");
        $sheet->setCellValue("U$rowIndex", "% Chiết khấu");
        $sheet->setCellValue("V$rowIndex", "Tiền Chiết khấu");
        $sheet->setCellValue("W$rowIndex", "%Thuế GTGT");
        $sheet->setCellValue("X$rowIndex", "Tiền thuế");
        $sheet->setCellValue("Y$rowIndex", "Diễn giải chi tiết");
        $sheet->setCellValue("Z$rowIndex", "TK Nợ");
        $sheet->setCellValue("AA$rowIndex", "TK Có");
        $sheet->setCellValue("AB$rowIndex", "Tiền sau Vat");
        $sheet->setCellValue("AC$rowIndex", "Tiền CK phân bổ");
        $sheet->setCellValue("AD$rowIndex", "Tiêu thức phân bổ");

        $sheet->getStyle("A$rowIndex:AD$rowIndex")->getFont()->setBold(true);
        $this->setBackgroundColor($sheet, "A$rowIndex:AD$rowIndex", CellColors::YELLOW);
        $this->setBorders($sheet, "A$rowIndex:AD$rowIndex");
    }

    /**
     * Content table
     * @param Sheet $sheet
     */
    function content(Sheet $sheet): void
    {
        $sortType = 'asc';
        $params = $this->params;
        $query = InvoiceDetail::query()->select(['id', 'invoice_id', 'product', 'unit', 'item_code_id', 'price', 'quantity', 'vat']);

        $query->whereHas('invoice', function ($q) {
            $q->where('type', InvoiceTypes::PURCHASE);
        });

        if (isset($params['sort'])) {
            $sort = $params['sort'];
            $sortType = $sort['type'] ?? 'asc';
        }

        if (isset($params['company_id'])) {
            $company_id = $params['company_id'];
            $query->whereHas('invoice', function ($q) use ($company_id) {
                $q->where('company_id', $company_id);
            });
        }

        if (isset($params['start_date']) && isset($params['end_date'])) {
            $start_date = $params['start_date'];
            $end_date = $params['end_date'];
            $query->whereHas('invoice', function ($q) use ($start_date, $end_date) {
                $q->whereDate('date', '>=', $start_date)->whereDate('date', '<=', $end_date);
            });
        }

        if (isset($params['price_from'])) {
            $price_from = $params['price_from'];
            $query->where('price', '>=', $price_from);
        }

        if (isset($params['price_to'])) {
            $price_to = $params['price_to'];
            $query->where('price', '<=', $price_to);
        }

        $records = $query->orderBy('product', $sortType)->get();

        foreach ($records as $index => $record) {
            $invoice = $record->invoice;
            $itemCode = $record->item_code;
            $rowIndex = $this->increaseIndex();
            $dateValue = $invoice->date;
            $productCode = "";
            if (isset($itemCode->product_code)) {
                if (empty($itemCode->item_group)) {
                    $productCode = $itemCode->product_code;
                } else {
                    $productCode = $itemCode->item_group->code ?? "";
                }
            }

            $carbonDate = Carbon::parse($dateValue);
            $excelDate = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($carbonDate);

            $this->setBorders($sheet, "A$rowIndex:AD$rowIndex");
            $sheet->getStyle("A$rowIndex")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_YYYYMMDD);
            $sheet->getStyle("E$rowIndex")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_YYYYMMDD);
            $sheet->getStyle("R$rowIndex:T$rowIndex")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->getStyle("X$rowIndex")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->getStyle("AB$rowIndex")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

            $sheet->setCellValue("A$rowIndex", $excelDate);
            $sheet->setCellValue("B$rowIndex", $invoice->invoice_number);
            $sheet->setCellValue("C$rowIndex", $invoice->invoice_symbol);
            $sheet->setCellValue("D$rowIndex", $invoice->invoice_number);
            $sheet->setCellValue("E$rowIndex", $excelDate);
            $sheet->setCellValue("F$rowIndex", "");
            $sheet->setCellValue("G$rowIndex", "Nhập kho");
            $sheet->setCellValue("H$rowIndex", $invoice->partner_tax_code);
            $sheet->setCellValue("I$rowIndex", $invoice->partner_name);
            $sheet->setCellValue("J$rowIndex", "Kho hàng hóa");
            $sheet->setCellValue("K$rowIndex", "");
            $sheet->setCellValue("L$rowIndex", "");
            $sheet->setCellValue("M$rowIndex", "");
            $sheet->setCellValue("N$rowIndex", "Nhập kho hàng hóa");
            $sheet->setCellValue("O$rowIndex", $productCode);
            $sheet->setCellValue("P$rowIndex", $record->product);
            $sheet->setCellValue("Q$rowIndex", trim($record->unit) == "/" ? "" : $record->unit);
            $sheet->setCellValue("R$rowIndex", $record->quantity);
            $sheet->setCellValue("S$rowIndex", $record->price);
            $sheet->setCellValue("T$rowIndex", "=R$rowIndex*S$rowIndex");
            $sheet->setCellValue("U$rowIndex", "");
            $sheet->setCellValue("V$rowIndex", "");
            $sheet->setCellValue("W$rowIndex", $record->vat);
            $sheet->setCellValue("X$rowIndex", "=T$rowIndex*W$rowIndex/100");
            $sheet->setCellValue("Y$rowIndex", $invoice->note ?? "Nhập kho hàng hóa");
            $sheet->setCellValue("Z$rowIndex", "1523");
            $sheet->setCellValue("AA$rowIndex", "1111");
            $sheet->setCellValue("AB$rowIndex", "=T$rowIndex+X$rowIndex");
            $sheet->setCellValue("AC$rowIndex", "");
            $sheet->setCellValue("AD$rowIndex", "");
        }
    }
}
