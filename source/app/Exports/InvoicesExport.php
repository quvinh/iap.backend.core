<?php

namespace App\Exports;

use App\Helpers\Enums\InvoiceCompleteStatusEnum;
use App\Helpers\Enums\InvoiceTypes;
use App\Models\Invoice;
use Carbon\Carbon;
use Maatwebsite\Excel\Excel;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class InvoicesExport implements FromCollection, Responsable, WithStyles, WithHeadings, WithColumnWidths, WithColumnFormatting
{
    use Exportable;

    private $sheet;
    private $record;

    public function __construct($record)
    {
        $this->record = $record;
    }

    /**
     * Optional Writer Type
     */
    private $writerType = Excel::XLSX;

    /**
     * Optional headers
     */
    private $headers = [
        'Content-Type' => 'text/csv',
    ];

    public function headings(): array
    {
        return [
            'STT',
            'Mẫu số',
            'Ký hiệu',
            'Số HĐ',
            'Tháng',
            'MST bên mua',
            'Tên bên mua',
            'MST bên bán',
            'Tên bên bán',
            'Tiền chưa thuế',
            'Tiền thuế',
            'Tổng tiền',
            'Trạng thái',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 7,
            'B' => 7,
            'C' => 7,
            'D' => 10,
            'E' => 15,
            'F' => 15,
            'G' => 25,
            'H' => 15,
            'I' => 25,
            'J' => 20,
            'K' => 20,
            'L' => 20,
            'M' => 15,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'J' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'K' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'L' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $count = count($this->record);
        $count++;
        return [
            "1" => ['font' => ['bold' => true]],
            "A1:M$count" => ['borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]]
        ];
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $result = array();
        foreach ($this->record as $index => $row) {
            $isSold = $row->type == InvoiceTypes::SOLD;
            $isPurchase = $row->type == InvoiceTypes::PURCHASE;
            # Check invoice locked
            if ($row->locked == 0) {
                $result[] = [
                    'index' => $index + 1,
                    'invoice_number_form' => $row->invoice_number_form,
                    'invoice_symbol' => $row->invoice_symbol,
                    'invoice_number' => $row->invoice_number,
                    'date' => Carbon::parse($row->date)->format('d/m/Y'),
                    'purchaser_tax_code' => $isPurchase ? $row->company[0]->tax_code : $row->partner_tax_code,
                    'purchaser' => $isPurchase ? $row->company[0]->tax_code : $row->partner_tax_code,
                    'seller_tax_code' => $isSold ? $row->company[0]->tax_code : $row->partner_tax_code,
                    'seller' => $isSold ? $row->company[0]->name : $row->partner_name,
                    'sum_money_no_vat' => $row->sum_money_no_vat,
                    'sum_money_vat' => $row->sum_money_vat,
                    'sum_money' => $row->sum_money,
                    'status' => $row->status == InvoiceCompleteStatusEnum::HOAN_THANH ? 'Hoàn thành' : '-',
                ];
            }
        }
        return collect($result);
    }
}
