<?php

namespace App\Repositories\TaxFreeVoucher;

use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\IdIsNotProvidedException;
use App\Helpers\Common\MetaInfo;
use App\Models\TaxFreeVoucher;
use App\Repositories\BaseRepository;
use App\Exceptions\DB\RecordIsNotFoundException as DBRecordIsNotFoundException;
use function Spatie\SslCertificate\starts_with;

class TaxFreeVoucherRepository extends BaseRepository implements ITaxFreeVoucherRepository
{
    /**
     * get corresponding model class name
     * @return string
     */
    function getRepositoryModelClass(): string
    {
        return TaxFreeVoucher::class;
    }
}
