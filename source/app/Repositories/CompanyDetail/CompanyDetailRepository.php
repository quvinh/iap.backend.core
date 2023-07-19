<?php

namespace App\Repositories\CompanyDetail;

use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\CannotUpdateDBException;
use App\Exceptions\DB\IdIsNotProvidedException;
use App\Helpers\Common\MetaInfo;
use App\Models\CompanyDetail;
use App\Repositories\BaseRepository;
use App\Exceptions\DB\RecordIsNotFoundException as DBRecordIsNotFoundException;
use App\Models\CompanyDetailAriseAccount;
use App\Models\CompanyDetailTaxFreeVoucher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

use function Spatie\SslCertificate\starts_with;

class CompanyDetailRepository extends BaseRepository implements ICompanyDetailRepository
{
    /**
     * Get corresponding model class name
     * @return string
     */
    function getRepositoryModelClass(): string
    {
        return CompanyDetail::class;
    }

    /**
     * Get company_detail_arise_accout
     */
    public function getSinglePropertyObject(mixed $idCom, mixed $idAcc): Builder
    {
        $query = (new CompanyDetailAriseAccount())->query();
        return $query->where([
            ['company_detail_id', $idCom],
            ['arise_account_id', $idAcc],
        ]);
    }

    /**
     * Create company_detail_arise_accout
     * @param array $param
     */
    public function createAriseAccount(array $param): Model
    {
        $entity = new CompanyDetailAriseAccount();
        $entity->setCompanyDetailAriseAccount($param['value_from'], $param['value_to']);
        $entity->company_detail_id = $param['company_detail_id'];
        $entity->arise_account_id = $param['arise_account_id'];
        $chk = $entity->save();

        if ($chk) {
            return $entity;
        } else {
            throw new CannotSaveToDBException();
        }
    }

    /**
     * Update company_detail_arise_accout
     * @param array $param
     */
    public function updateAriseAccount(array $param): Model
    {
        if (!in_array('id', array_keys($param))) throw new IdIsNotProvidedException();
        $entity = (new CompanyDetailAriseAccount())->query()->where('id', $param['id'])->first();
        if ($entity === null)
            throw new DBRecordIsNotFoundException();
        $entity->setCompanyDetailAriseAccount($param['value_from'], $param['value_to']);
        $chk = $entity->save();

        if ($chk) {
            return $entity;
        } else {
            throw new CannotUpdateDBException();
        }
    }

    /**
     * Delete id not in ids company_detail_arise_accout
     * @param array $ids
     */
    public function deleteAriseAccount(mixed $idCom, array $ids): bool
    {
        $list = (new CompanyDetailAriseAccount())->query()->where('company_detail_id', $idCom)->get('arise_account_id')->toArray();
        
        $needDelete = array_filter($list, function ($item) use ($ids) {
            return !in_array($item['arise_account_id'], $ids);
        });

        foreach ($needDelete as $item) {
            (new CompanyDetailAriseAccount())->query()->where('arise_account_id', $item['arise_account_id'])->delete();         
        }
        return true;
    }

    /**
     * Create company_detail_tax_free_voucher
     * @param array $param
     */
    public function createTaxFreeVoucher(array $param): Model
    {
        $entity = new CompanyDetailTaxFreeVoucher();
        $entity->company_detail_id = $param['company_detail_id'];
        $entity->tax_free_voucher_id = $param['tax_free_voucher_id'];
        $chk = $entity->save();

        if ($chk) {
            return $entity;
        } else {
            throw new CannotSaveToDBException();
        }
    }

    /**
     * Delete id not in ids company_detail_tax_free_voucher
     * @param array $ids
     */
    public function deleteTaxFreeVoucher(mixed $idCom, array $ids): bool
    {
        $list = (new CompanyDetailTaxFreeVoucher())->query()->where('company_detail_id', $idCom)->get('tax_free_voucher_id')->toArray();
        
        $needDelete = array_filter($list, function ($item) use ($ids) {
            return !in_array($item['tax_free_voucher_id'], $ids);
        });

        foreach ($needDelete as $item) {
            (new CompanyDetailAriseAccount())->query()->where('tax_free_voucher_id', $item['tax_free_voucher_id'])->delete();         
        }
        return true;
    }
}
