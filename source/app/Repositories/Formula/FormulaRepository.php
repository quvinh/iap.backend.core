<?php

namespace App\Repositories\Formula;

use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\IdIsNotProvidedException;
use App\Helpers\Common\MetaInfo;
use App\Models\Formula;
use App\Repositories\BaseRepository;
use App\Exceptions\DB\RecordIsNotFoundException as DBRecordIsNotFoundException;
use function Spatie\SslCertificate\starts_with;

class FormulaRepository extends BaseRepository implements IFormulaRepository
{
    /**
     * get corresponding model class name
     * @return string
     */
    function getRepositoryModelClass(): string
    {
        return Formula::class;
    }
}
