<?php

namespace App\Repositories\Permission;

use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\IdIsNotProvidedException;
use App\Helpers\Common\MetaInfo;
use App\Models\Permission;
use App\Repositories\BaseRepository;
use App\Exceptions\DB\RecordIsNotFoundException as DBRecordIsNotFoundException;
use function Spatie\SslCertificate\starts_with;

class PermissionRepository extends BaseRepository implements IPermissionRepository
{
    /**
     * get corresponding model class name
     * @return string
     */
    function getRepositoryModelClass(): string
    {
        return Permission::class;
    }

    public function findBySlug($slug): Permission | null
    {
        return Permission::query()->where('slug', $slug)->first();
    }
}
