<?php

namespace App\Repositories\Company;

use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\IdIsNotProvidedException;
use App\Helpers\Common\MetaInfo;
use App\Models\Company;
use App\Repositories\BaseRepository;
use App\Exceptions\DB\RecordIsNotFoundException as DBRecordIsNotFoundException;
use App\Helpers\Enums\InvoiceTypes;
use App\Models\Formula;
use App\Models\Invoice;
use App\Models\ItemCode;
use App\Models\UserCompany;
use Illuminate\Support\Collection;

use function Spatie\SslCertificate\starts_with;

class CompanyRepository extends BaseRepository implements ICompanyRepository
{
    /**
     * get corresponding model class name
     * @return string
     */
    function getRepositoryModelClass(): string
    {
        return Company::class;
    }

    /**
     * Get all companies
     */
    public function getAllCompanies(): Collection
    {
        // $userId = auth()->user()->getAuthIdentifier();
        // $userCompanies = UserCompany::where('user_id', $userId)->get('company_id')->toArray();
        // $arr = [];
        // if (!empty($userCompanies)) {
        //     $arr = array_map(function ($item) {
        //         return $item['company_id'];
        //     }, $userCompanies);
        // }
        $companies = Company::where('status', 1)->orderByDesc('id')->get();
        return $companies;
    }

    /**
     * Get list inventory by company
     */
    public function inventory(mixed $company_id, string $start, string $end): array
    {
        $result = array();
        $formulas = Formula::query()->select('formulas.id', 'formulas.sum_avg')
            ->join('company_details', 'company_details.id', '=', 'formulas.company_detail_id')
            ->where([
                ['company_details.company_id', '=', 29],
                ['formulas.status', '=', 1],
            ])->get()->toArray();

        $invoices = Invoice::query()->select(
            'invoices.id',
            'invoices.type',
            'invoice_details.item_code_id',
            'invoice_details.formula_path_id',
            'invoice_details.vat_money',
            'invoice_details.total_money'
        )
            ->join('invoice_details', 'invoice_details.invoice_id', '=', 'invoices.id')
            ->where([
                ['invoices.company_id', '=', $company_id],
                ['invoices.date', '>=', $start],
                ['invoices.date', '<=', $end],
            ])
            ->whereNotNull('invoice_details.formula_path_id')->get();

        foreach ($invoices as $invoice) {
            if (empty($result[$invoice->item_code_id])) {
                # Search formula
                $f_id = explode(',', $invoice->formula_path_id)[0] ?? null;
                if (empty($f_id)) continue;
                $formula = Formula::find($f_id);
                if (empty($formula) || empty($formula->id)) continue;

                # Search item code
                $item_code = ItemCode::find($invoice->item_code_id);
                if (empty($item_code) || empty($item_code->id)) continue;


                $purchase = $invoice->type == InvoiceTypes::PURCHASE ? ($invoice->total_money * 1.0) : 0;
                $sold = $invoice->type == InvoiceTypes::SOLD ? ($invoice->total_money * 1.0) : 0;

                # Push array
                $result[$invoice->item_code_id] = [
                    'item_code_id' => $item_code->id,
                    'product_code' => $item_code->product_code,
                    'product_exchange' => $item_code->product_exchange ?? '/',
                    'unit' => $item_code->unit ?? '/',
                    'opening_balance' => $item_code->opening_balance_value * 1.0,
                    'purchase' => $purchase,
                    'sold' => $sold,
                    'cost_price_sold' => $sold * $formula->sum_avg * 0.01,
                ];
            } else {
                # Search formula
                $f_id = explode(',', $invoice->formula_path_id)[0] ?? null;
                if (empty($f_id)) continue;
                $formula = Formula::find($f_id);
                if (empty($formula) || empty($formula->id)) continue;

                $purchase = $invoice->type == InvoiceTypes::PURCHASE ? ($invoice->total_money * 1.0) : 0;
                $sold = $invoice->type == InvoiceTypes::SOLD ? ($invoice->total_money * 1.0) : 0;
                $mutate = (object) $result[$invoice->item_code_id];
                $mutate->purchase += $purchase;
                $mutate->sold += $sold;
                $mutate->cost_price_sold += $sold * $formula->sum_avg * 0.01;

                # Mutate array
                $result[$invoice->item_code_id] = (array) $mutate;
            }
        }
        return array_values($result);
    }

    /**
     * Add UserCompany management
     * @param Company $record
     */
    public function addUserCompany(Company $record): bool
    {
        if (empty($record->id)) return false;
        $user_id = auth()->user()->getAuthIdentifier();
        $userCompany = new UserCompany();
        $userCompany->user_id = $user_id;
        $userCompany->company_id = $record->id;
        return $userCompany->save();
    }

    public function updateUserAssignments(Company $record, array $userIds)
    {
        $record->userAssignments()->delete();
        foreach ($userIds as $userId) {
            $record->userAssignments()->create([
                'user_id' => $userId
            ]);
        }
    }
}
