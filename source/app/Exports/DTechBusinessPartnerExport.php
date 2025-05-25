<?php

namespace App\Exports;

use App\Helpers\Enums\CellColors;
use App\Helpers\Utils\StringHelper;
use App\Models\BusinessPartner;
use App\Models\Company;
use App\Models\InvoiceDetail;
use App\Models\ItemCode;
use App\Models\LotPlan;
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

final class DTechBusinessPartnerExport implements WithEvents
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
        $columns = ['B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];

        $sheet->getColumnDimension('A')->setWidth(27);
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
        if (isset($params['company_id']) && isset($params['year'])) {
            $year = $params['year'];
            $company = Company::find($params['company_id']);
            if (!empty($company)) {
                $title = ": {$company->name} Năm {$year}";
            }
        }

        $sheet->setCellValue("A$rowIndex", "Khách hàng/Nhà cung cấp{$title}");
        $rowIndex = $this->increaseIndex();

        $sheet->setCellValue("A$rowIndex", "Mã");
        $sheet->setCellValue("B$rowIndex", "Tên đối tượng công nợ");
        $sheet->setCellValue("C$rowIndex", "Mã số thuế");
        $sheet->setCellValue("D$rowIndex", "Địa chỉ");
        $sheet->setCellValue("E$rowIndex", "Email");
        $sheet->setCellValue("F$rowIndex", "Điện thoại");
        $sheet->setCellValue("G$rowIndex", "Fax");
        $sheet->setCellValue("H$rowIndex", "Là nhân viên");
        $sheet->setCellValue("I$rowIndex", "CMND/CCCD");
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
        $sortType = 'desc';
        $query = BusinessPartner::query();
        $params = $this->params;
        
        if (isset($params['sort'])) {
            $sort = $params['sort'];
            $sortType = $sort['type'] ?? 'desc';
        }

        if (isset($params['company_id'])) {
            $company_id = $params['company_id'];
            $query->where('company_id', '=', $company_id);
        }

        $records = $query->orderBy('id', $sortType)->get();

        foreach ($records as $index => $record) {
            $rowIndex = $this->increaseIndex();
            $validTaxCode = StringHelper::isValidTaxCode($record['tax_code']) ? $record['tax_code'] : "";
            $this->setBorders($sheet, "A$rowIndex:J$rowIndex");
            // $sheet->getStyle("E$rowIndex:I$rowIndex")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->setCellValue("A$rowIndex", $record['tax_code']);
            $sheet->setCellValue("B$rowIndex", $record['name']);
            $sheet->setCellValue("C$rowIndex", $validTaxCode);
            $sheet->setCellValue("D$rowIndex", $record['address']);
            $sheet->setCellValue("E$rowIndex", $record['email']);
            $sheet->setCellValue("F$rowIndex", $record['phone']);
            $sheet->setCellValue("G$rowIndex", "");
            $sheet->setCellValue("H$rowIndex", "");
            $sheet->setCellValue("I$rowIndex", "");
            $sheet->setCellValue("J$rowIndex", "");
        }
    }
}
