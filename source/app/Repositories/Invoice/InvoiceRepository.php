<?php

namespace App\Repositories\Invoice;

use App\DataResources\BaseDataResource;
use App\DataResources\Invoice\InvoiceBasicResource;
use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\IdIsNotProvidedException;
use App\Helpers\Common\MetaInfo;
use App\Models\Invoice;
use App\Repositories\BaseRepository;
use App\Exceptions\DB\RecordIsNotFoundException as DBRecordIsNotFoundException;
use App\Helpers\Enums\InvoiceCompleteStatusEnum;
use App\Helpers\Enums\InvoiceTypes;
use App\Models\BusinessPartner;
use App\Models\Company;
use App\Models\InvoiceDetail;
use App\Models\InvoiceTask;
use App\Models\UserCompany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

use function Spatie\SslCertificate\starts_with;

class InvoiceRepository extends BaseRepository implements IInvoiceRepository
{
    /**
     * get corresponding model class name
     * @return string
     */
    function getRepositoryModelClass(): string
    {
        return Invoice::class;
    }

    /**
     * Delete id not in ids invoice-details
     * @param array $ids
     */
    public function deleteInvoiceDetails(mixed $idInvoice, array $ids): bool
    {
        $list = (new InvoiceDetail())->query()->where('invoice_id', $idInvoice)->get(['id'])->toArray();

        $needDelete = array_filter($list, function ($item) use ($ids) {
            return !in_array($item['id'], $ids);
        });
        foreach ($needDelete as $item) {
            (new InvoiceDetail())->query()->where('id', $item['id'])->forceDelete();
        }
        return true;
    }

    /**
     * Find partners by company_id
     */
    public function findPartnersByCompanyId(mixed $company_id, mixed $year): mixed
    {
        $partners = (new Invoice())->query()
            ->where('company_id', $company_id)
            ->whereYear('date', $year)
            ->groupBy('partner_tax_code')
            ->orderByDesc('id')
            ->select('id', 'partner_name', 'partner_tax_code', 'partner_address')
            ->get();
        return $partners;
    }

    /**
     * Sum sold money
     * Sum purchase money
     * Sum invoices have verification_code_status = 0
     * Sum invoices aren't used
     */
    public function info(array $params): array
    {
        $query = Invoice::query();

        # Check companies authoritied
        $userId = auth()->user()->getAuthIdentifier();
        $userCompanies = UserCompany::where('user_id', $userId)->get('company_id')->toArray();
        $arr = [];
        if (!empty($userCompanies)) {
            $arr = array_map(function ($item) {
                return $item['company_id'];
            }, $userCompanies);
        }
        $query->whereIn('company_id', $arr);

        if (isset($params['company_id'])) {
            $query->where('company_id', '=', $params['company_id']);
        }
        if (isset($params['invoice_number'])) {
            $query->where('invoice_number', '=', $params['invoice_number']);
        }
        if (isset($params['status'])) {
            $query->where('status', '=', $params['status']);
        }
        if (isset($params['type'])) {
            $query->where('type', '=', $params['type']);
        }
        if (isset($params['verification_code_status'])) {
            $query->where('verification_code_status', '=', $params['verification_code_status']);
        }
        if (isset($params['locked'])) {
            $query->where('locked', '=', $params['locked']);
        }
        if (isset($params['date']) && isset($params['date']['from']) && isset($params['date']['to'])) {
            $query->whereDate('date', '>=', $params['date']['from'])->whereDate('date', '<=', $params['date']['to']);
        }
        $invoices = $query->get();

        // if (empty($invoices)) return [];
        $sumSold = $sumPurchase = $sumInvoiceNotVerificated = $sumInvoiceNotUse = $complete = 0;
        $sumMoney = $sumMoneyNoVat = $sumMoneyVat = 0;
        foreach ($invoices as $invoice) {
            $type = $invoice->type;
            $verification_code_status = $invoice->verification_code_status;
            $locked = $invoice->locked; # Locked: HD khong duoc su dung de hoach toan 
            if ($type == InvoiceTypes::SOLD) $sumSold++;
            if ($type == InvoiceTypes::PURCHASE) $sumPurchase++;
            if ($verification_code_status == 0) $sumInvoiceNotVerificated++;
            if ($locked == 1) $sumInvoiceNotUse++;
            if ($invoice->status == InvoiceCompleteStatusEnum::HOAN_THANH) $complete++;

            if ($locked == 0) {
                $sumMoney += $invoice->sum_money;
                $sumMoneyNoVat += $invoice->sum_money_no_vat;
                $sumMoneyVat += $invoice->sum_money_vat;
            }
        }
        return [
            'sum_sold' => $sumSold,
            'sum_purchase' => $sumPurchase,
            'no_verification_code' => $sumInvoiceNotVerificated,
            'locked' => $sumInvoiceNotUse,
            'complete' => $complete,
            'total' => count($invoices),
            'sum_money' => $sumMoney,
            'sum_money_no_vat' => $sumMoneyNoVat,
            'sum_money_vat' => $sumMoneyVat,
        ];
    }

    /**
     * Find invoice previous/next
     */
    public function findNextInvoice(array $params): Invoice | Collection | null
    {
        $record = Invoice::find($params['invoice_id'] ?? 0);
        if (empty($record)) throw new \Exception("Invoice not found!");
        $company_id = $params['company_id'] ?? 0;
        $type = $params['type'] ?? '';
        $operate = $params['operate'] ?? '>=';
        $invoices = Invoice::query()
            ->where([
                ['company_id', '=', $company_id],
                ['type', '=', $type],
                ['status', '<>', 2],
            ])
            ->whereDate('date', $operate, $record->date)
            ->orderBy('date')->orderBy('invoice_number')->take(100)->get();
        $position = 0;
        foreach ($invoices as $index => $invoice) {
            if ($invoice->id == $record->id) $position = $index;
        }
        if ($operate == '>=') return $invoices[$position + 1] ?? null;
        if ($operate == '<=') return $invoices[$position - 1] ?? null;
        return null;
    }

    /**
     * Report sold
     */
    public function reportSold(array $params): Collection | Invoice | array | null
    {
        $invoices = Invoice::query()->where([
            ['type', '=', InvoiceTypes::SOLD],
            ['locked', '=', 0],
            ['company_id', '=', $params['company_id']],
            ['date', '>=', $params['start']],
            ['date', '<=', $params['end']],
        ])->orderBy('date')->get();
        // $result = BaseDataResource::generateResources($invoices, InvoiceBasicResource::class, ['invoice_details', 'company']);
        return $invoices;
    }

    /**
     * Create invoice from TCT
     */
    public function createInvoiceTct(array $param): Invoice
    {
        # Check task
        $company_id = $param['company_id'];
        $task_month = Carbon::parse($param['date'])->format('m/Y');
        $task = InvoiceTask::query()->where([
            ['company_id', '=', $company_id],
            ['month_of_year', '=', $task_month],
        ])->first();
        if (empty($task)) {
            $task = new InvoiceTask();
            $task->company_id = $company_id;
            $task->month_of_year = $task_month;
            $task->save();
        }

        # Checked invoice from front end
        # Create invoice-tct
        $invoice = new Invoice();
        $invoice->company_id = $company_id;
        $invoice->invoice_task_id = $task->id;
        $invoice->partner_tax_code = $param['partner_tax_code'];
        $invoice->partner_name = $param['partner_name'] ?? null;
        $invoice->partner_address = $param['partner_address'] ?? null;
        $invoice->type = $param['type'];
        $invoice->invoice_number = $param['invoice_number'];
        $invoice->invoice_symbol = $param['invoice_symbol'];
        $invoice->date = $param['date'];
        $invoice->invoice_number_form = $param['invoice_number_form'] ?? 1; # Warning
        $invoice->verification_code_status = $param['verification_code_status'] ?? 1; # Co ma co quan thue
        $invoice->status = $param['status'] ?? InvoiceCompleteStatusEnum::DA_XU_LY;
        $invoice->sum_money_no_vat = $param['sum_money_no_vat'] ?? 0;
        $invoice->sum_money_vat = $param['sum_money_vat'] ?? 0;
        $invoice->sum_money = $param['sum_money'] ?? 0;
        $invoice->property = $param['property'] ?? 0;
        $invoice->invoice_status = $param['invoice_status'] ?? null;
        $invoice->processing_status = $param['processing_status'] ?? null;
        $invoice->created_by = auth()->user()->id . '|' . auth()->user()->name;
        $invoice->save();

        # Create invoice-detail
        $invoice_details = $param['invoice_details'];
        foreach ($invoice_details as $row) {
            $invoiceDetail = new InvoiceDetail();
            $invoiceDetail->invoice_id = $invoice->id;
            // $invoiceDetail->item_code_id = null;
            $invoiceDetail->product = $row['product'];
            $invoiceDetail->product_exchange = $param['product_exchange'] ?? null;
            $invoiceDetail->unit = $row['unit'] ?? '/';
            $invoiceDetail->quantity = $row['quantity'] ?? 0;
            $invoiceDetail->price = $row['price'] ?? 0;
            $invoiceDetail->vat = $row['vat'] ?? 0;
            $invoiceDetail->vat_money = $row['vat_money'] ?? 0;
            $invoiceDetail->total_money = $row['total_money'] ?? 0;
            $invoiceDetail->save();
        }

        # Return
        return $invoice;
    }

    /**
     * Create invoices from TCT
     */
    public function createInvoicesTct(array $param): mixed
    {
        foreach ($param['records'] as $record) {
            $partner_tax_code = $record['partner_tax_code'];
            $invoice_number = (int) ($record['invoice_number'] ?? 0);
            $invoice_symbol = $record['invoice_symbol'];
            $item = Invoice::where([
                ['company_id', '=', $record['company_id']],
                ['partner_tax_code', '=', $partner_tax_code],
                ['invoice_number', '=', $invoice_number],
                ['invoice_symbol', '=', $invoice_symbol],
            ])->whereYear('date', '=', Carbon::parse($record['date'])->format('Y'))->first();

            if (empty($item)) {
                self::createInvoiceTct($record);
            }
        }
        return true;
    }

    /**
     * Save invoice from TCT
     */
    public function saveInvoiceTct(array $param): mixed
    {
        $company_id = $param['company_id'];
        $type = $param['type'];
        $year = $param['year'];

        foreach ($param['records'] as $record) {
            # Check invoice
            $invoice = Invoice::query()
                ->whereYear('date', '=', $year)
                ->where([
                    ['company_id', '=', $company_id],
                    ['type', '=', $type],
                    ['partner_tax_code', '=', $record['partner_tax_code']],
                    ['invoice_number', '=', $record['invoice_number']],
                    ['invoice_symbol', '=', $record['invoice_symbol']],
                ])->first();
            if (empty($invoice)) {
                $partner = BusinessPartner::where('tax_code', $record['partner_tax_code'])->first();
                if (empty($partner)) BusinessPartner::create([
                    'company_id' => $company_id,
                    'tax_code' => $record['partner_tax_code'],
                    'name' => $record['partner_name'] ?? $record['partner_tax_code'],
                ]);

                self::createInvoiceTct($record);
            }
        }
        return true;
    }

    public function checkInvoiceExist(array $param): mixed
    {
        $companyId = $param['company_id'];
        $invoiceType = $param['invoice_type'];
        $year = $param['year'];
        $records = $param['records'];
        $result = [];

        foreach ($records as $record) {
            $partner_tax_code = $invoiceType == InvoiceTypes::SOLD ? $record['nmmst'] : $record['nbmst'];
            $invoice_number = (int) ($record['shdon'] ?? 0);
            $invoice_symbol = $record['khhdon'];
            $item = Invoice::where([
                ['company_id', '=', $companyId],
                ['partner_tax_code', '=', $partner_tax_code],
                ['invoice_number', '=', $invoice_number],
                ['invoice_symbol', '=', $invoice_symbol],
            ])->whereYear('date', '=', $year)->first();

            if (!empty($item)) {
                $result[] = array_merge($item->toArray(), [
                    'invoice_tct_id' => $record['id'],
                ]);
            }
        }

        return $result;
    }
}
