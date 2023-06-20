<?php

namespace App\Repositories\CompanyDetailTaxFreeVoucher;

use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\IdIsNotProvidedException;
use App\Helpers\Common\MetaInfo;
use App\Models\CompanyDetailTaxFreeVoucher;
use App\Repositories\BaseRepository;
use App\Exceptions\DB\RecordIsNotFoundException as DBRecordIsNotFoundException;
use function Spatie\SslCertificate\starts_with;

class CompanyDetailTaxFreeVoucherRepository extends BaseRepository implements ICompanyDetailTaxFreeVoucherRepository
{
    /**
     * get corresponding model class name
     * @return string
     */
    function getRepositoryModelClass(): string
    {
        return CompanyDetailTaxFreeVoucher::class;
    }
}
