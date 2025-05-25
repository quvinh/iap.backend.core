<?php

namespace App\Exports;

use App\Helpers\Enums\CellColors;
use App\Helpers\Enums\InvoiceTypes;
use App\Helpers\Utils\StringHelper;
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

final class DTechInvoiceSoldExport implements WithEvents
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
            'AD',
            'AE',
            'AF',
            'AG',
            'AH',
            'AI',
            'AJ',
            'AK',
            'AL',
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

        $sheet->setCellValue("A$rowIndex", "Bán ra{$title}");
        $rowIndex = $this->increaseIndex();

        $sheet->setCellValue("A$rowIndex", "Ngày");
        $sheet->setCellValue("B$rowIndex", "Chứng từ");
        $sheet->setCellValue("C$rowIndex", "Seri hóa đơn");
        $sheet->setCellValue("D$rowIndex", "Số hóa đơn");
        $sheet->setCellValue("E$rowIndex", "Ngày hóa đơn");
        $sheet->setCellValue("F$rowIndex", "Ông/ Bà");
        $sheet->setCellValue("G$rowIndex", "Diễn giải");
        $sheet->setCellValue("H$rowIndex", "Mã đối tượng");
        $sheet->setCellValue("I$rowIndex", "Đối tượng");
        $sheet->setCellValue("J$rowIndex", "Kh thuế");
        $sheet->setCellValue("K$rowIndex", "Địa chỉ");
        $sheet->setCellValue("L$rowIndex", "Mã số thuế");
        $sheet->setCellValue("M$rowIndex", "Email");
        $sheet->setCellValue("N$rowIndex", "Kho");
        $sheet->setCellValue("O$rowIndex", "Mã Nhân viên");
        $sheet->setCellValue("P$rowIndex", "Nhân viên");
        $sheet->setCellValue("Q$rowIndex", "TK ngân hàng Nợ");
        $sheet->setCellValue("R$rowIndex", "Ngân hàng Nợ");
        $sheet->setCellValue("S$rowIndex", "HT thanh toán");
        $sheet->setCellValue("T$rowIndex", "Ghi chú thêm");
        $sheet->setCellValue("U$rowIndex", "Lý do");
        $sheet->setCellValue("V$rowIndex", "Mã Vật tư");
        $sheet->setCellValue("W$rowIndex", "Tên Vật tư hàng hóa");
        $sheet->setCellValue("X$rowIndex", "Đv đo");
        $sheet->setCellValue("Y$rowIndex", "Số lượng tồn");
        $sheet->setCellValue("Z$rowIndex", "Số lượng");
        $sheet->setCellValue("AA$rowIndex", "Đơn giá");
        $sheet->setCellValue("AB$rowIndex", "Thành tiền");
        $sheet->setCellValue("AC$rowIndex", "Tiền giảm theo NQ");
        $sheet->setCellValue("AD$rowIndex", "Thanh Toán");
        $sheet->setCellValue("AE$rowIndex", "Ngành nghề kinh doanh");
        $sheet->setCellValue("AF$rowIndex", "Công trình");
        $sheet->setCellValue("AG$rowIndex", "Vụ việc");
        $sheet->setCellValue("AH$rowIndex", "Diễn giải chi tiết");
        $sheet->setCellValue("AI$rowIndex", "TK Nợ");
        $sheet->setCellValue("AJ$rowIndex", "TK Có");
        $sheet->setCellValue("AK$rowIndex", "Nhóm hàng");
        $sheet->setCellValue("AL$rowIndex", "DVT Nhóm");

        $sheet->getStyle("A$rowIndex:AL$rowIndex")->getFont()->setBold(true);
        $this->setBackgroundColor($sheet, "A$rowIndex:AL$rowIndex", CellColors::YELLOW);
        $this->setBorders($sheet, "A$rowIndex:AL$rowIndex");
    }

    /**
     * Content table
     * @param Sheet $sheet
     */
    function content(Sheet $sheet): void
    {
        $sortType = 'desc';
        $params = $this->params;
        $query = InvoiceDetail::query()->select(['id', 'invoice_id', 'product', 'unit', 'item_code_id', 'price', 'quantity', 'vat']);

        $query->whereHas('invoice', function ($q) {
            $q->where('type', InvoiceTypes::SOLD);
        });

        if (isset($params['sort'])) {
            $sort = $params['sort'];
            $sortType = $sort['type'] ?? 'desc';
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

        $records = $query->orderBy('id', $sortType)->get();

        foreach ($records as $index => $record) {
            $invoice = $record->invoice;
            $itemCode = $record->item_code;
            $rowIndex = $this->increaseIndex();
            $dateValue = $invoice->date;
            $carbonDate = Carbon::parse($dateValue);
            $excelDate = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($carbonDate);
            $this->setBorders($sheet, "A$rowIndex:AL$rowIndex");
            $sheet->getStyle("A$rowIndex")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_YYYYMMDD);
            $sheet->getStyle("E$rowIndex")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_YYYYMMDD);
            $sheet->getStyle("AA$rowIndex:AD$rowIndex")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

            $sheet->setCellValue("A$rowIndex", $excelDate);
            $sheet->setCellValue("B$rowIndex", $invoice->invoice_number);
            $sheet->setCellValue("C$rowIndex", $invoice->invoice_symbol);
            $sheet->setCellValue("D$rowIndex", $invoice->invoice_number);
            $sheet->setCellValue("E$rowIndex", $excelDate);
            $sheet->setCellValue("F$rowIndex", "");
            $sheet->setCellValue("G$rowIndex", "Bán hàng");
            $sheet->setCellValue("H$rowIndex", $invoice->partner_tax_code);
            $sheet->setCellValue("I$rowIndex", $invoice->partner_name);
            $sheet->setCellValue("J$rowIndex", "");
            $sheet->setCellValue("K$rowIndex", $invoice->partner_address);
            $sheet->setCellValue("L$rowIndex", StringHelper::isValidTaxCode($invoice->partner_tax_code) ? $invoice->partner_tax_code : "");
            $sheet->setCellValue("M$rowIndex", "");
            $sheet->setCellValue("N$rowIndex", "Kho hàng hóa");
            $sheet->setCellValue("O$rowIndex", "");
            $sheet->setCellValue("P$rowIndex", "");
            $sheet->setCellValue("Q$rowIndex", "");
            $sheet->setCellValue("R$rowIndex", "");
            $sheet->setCellValue("S$rowIndex", "TM/CK");
            $sheet->setCellValue("T$rowIndex", "");
            $sheet->setCellValue("U$rowIndex", "Bán hàng hóa, thành phẩm");
            $sheet->setCellValue("V$rowIndex", $itemCode->product_code ?? "");
            $sheet->setCellValue("W$rowIndex", $record->product);
            $sheet->setCellValue("X$rowIndex", trim($record->unit) == "/" ? "" : $record->unit);
            $sheet->setCellValue("Y$rowIndex", isset($itemCode->quantity) ? $itemCode->quantity : "");
            $sheet->setCellValue("Z$rowIndex", $record->quantity);
            $sheet->setCellValue("AA$rowIndex", $record->price);
            $sheet->setCellValue("AB$rowIndex", "=Z$rowIndex*AA$rowIndex");
            $sheet->setCellValue("AC$rowIndex", 0);
            $sheet->setCellValue("AD$rowIndex", "=AB$rowIndex-AC$rowIndex");
            $sheet->setCellValue("AE$rowIndex", "Hoạt động bán buôn, bán lẻ các loại hàng hóa (trừ giá trị hàng hóa đại lý bán đúng giá hưởng hoa hồng)");
            $sheet->setCellValue("AF$rowIndex", "");
            $sheet->setCellValue("AG$rowIndex", "");
            $sheet->setCellValue("AH$rowIndex", $invoice->note ?? "");
            $sheet->setCellValue("AI$rowIndex", "1111");
            $sheet->setCellValue("AJ$rowIndex", "91111");
            $sheet->setCellValue("AK$rowIndex", "0");
            $sheet->setCellValue("AL$rowIndex", "");
        }
    }
}
