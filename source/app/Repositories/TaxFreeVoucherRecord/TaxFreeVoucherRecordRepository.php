<?php

namespace App\Repositories\TaxFreeVoucherRecord;

use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\IdIsNotProvidedException;
use App\Helpers\Common\MetaInfo;
use App\Models\TaxFreeVoucherRecord;
use App\Repositories\BaseRepository;
use App\Exceptions\DB\RecordIsNotFoundException as DBRecordIsNotFoundException;
use function Spatie\SslCertificate\starts_with;

class TaxFreeVoucherRecordRepository extends BaseRepository implements ITaxFreeVoucherRecordRepository
{
    /**
     * get corresponding model class name
     * @return string
     */
    function getRepositoryModelClass(): string
    {
        return TaxFreeVoucherRecord::class;
    }
}
