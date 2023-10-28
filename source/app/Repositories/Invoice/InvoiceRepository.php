<?php

namespace App\Repositories\Invoice;

use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\IdIsNotProvidedException;
use App\Helpers\Common\MetaInfo;
use App\Models\Invoice;
use App\Repositories\BaseRepository;
use App\Exceptions\DB\RecordIsNotFoundException as DBRecordIsNotFoundException;
use App\Models\InvoiceDetail;

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
}
