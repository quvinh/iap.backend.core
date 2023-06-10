<?php

namespace App\Repositories\CompanyType;

use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\IdIsNotProvidedException;
use App\Helpers\Common\MetaInfo;
use App\Models\CompanyType;
use App\Repositories\BaseRepository;
use App\Exceptions\DB\RecordIsNotFoundException as DBRecordIsNotFoundException;
use function Spatie\SslCertificate\starts_with;

class CompanyTypeRepository extends BaseRepository implements ICompanyTypeRepository
{
    /**
     * get corresponding model class name
     * @return string
     */
    function getRepositoryModelClass(): string
    {
        return CompanyType::class;
    }
}
