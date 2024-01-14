<?php

namespace App\Exports;

use App\Helpers\Enums\InvoiceTypes;
use App\Models\Invoice;
use Maatwebsite\Excel\Excel;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InvoiceDetailsExport implements FromCollection, Responsable, WithStyles, WithHeadings, WithColumnWidths
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
            'Mẫu số',
            'Ký hiệu',
            'Tháng',
            'Số HĐ',
            'MST bên mua',
            'Tên bên mua',
            'MST bên bán',
            'Tên bên bán',
            'Tên hàng hoá, dịch vụ',
            'ĐVT',
            'Số lượng',
            'Đơn giá',
            'Tổng cộng',
            'Thuế suất',
            'Tiền thuế',
            'Trạng thái',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 7,
            'B' => 7,
            'C' => 15,
            'D' => 10,
            'E' => 15,
            'F' => 25,
            'G' => 15,
            'H' => 25,
            'I' => 30,
            'J' => 10,
            'K' => 10,
            'L' => 15,
            'M' => 15,
            'N' => 5,
            'O' => 15,
            'Q' => 5,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $result = array();
        foreach ($this->record as $row) {
            $isSold = $row->type == InvoiceTypes::SOLD;
            $isPurchase = $row->type == InvoiceTypes::PURCHASE;
            foreach ($row->invoice_details as $item) {
                $result[] = [
                    'invoice_number_form' => $row->invoice_number_form,
                    'invoice_symbol' => $row->invoice_symbol,
                    'date' => $row->date,
                    'invoice_number' => $row->invoice_number,
                    'purchaser_tax_code' => $isPurchase ? $row->company[0]->tax_code : $row->partner_tax_code,
                    'purchaser' => $isPurchase ? $row->company[0]->tax_code : $row->partner_tax_code,
                    'seller_tax_code' => $isSold ? $row->company[0]->tax_code : $row->partner_tax_code,
                    'seller' => $isSold ? $row->company[0]->name : $row->partner_name,
                    # item-detail
                    'product' => $item->product,
                    'unit' => $item->unit,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total_money' => $item->total_money,
                    'vat' => $item->vat,
                    'vat_money' => $item->vat_money,

                    'status' => $row->status == 2 ? 'Hoàn thành' : '-',
                ];
            }
        }
        return collect($result);
    }
}
