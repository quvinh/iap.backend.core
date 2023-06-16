<?php

namespace App\Repositories\CompanyDetail;

use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\IdIsNotProvidedException;
use App\Helpers\Common\MetaInfo;
use App\Models\CompanyDetail;
use App\Repositories\BaseRepository;
use App\Exceptions\DB\RecordIsNotFoundException as DBRecordIsNotFoundException;
use App\Models\CompanyDetailAriseAccount;
use Illuminate\Database\Eloquent\Model;

use function Spatie\SslCertificate\starts_with;

class CompanyDetailRepository extends BaseRepository implements ICompanyDetailRepository
{
    /**
     * get corresponding model class name
     * @return string
     */
    function getRepositoryModelClass(): string
    {
        return CompanyDetail::class;
    }

    /**
     * Create company_detail_arise_accout
     * @param array $param
     */
    public function ariseAccount(array $param): Model
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
}
