<?php

namespace App\Repositories\PdfTableKey;

use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\IdIsNotProvidedException;
use App\Helpers\Common\MetaInfo;
use App\Models\PdfTableKey;
use App\Repositories\BaseRepository;
use App\Exceptions\DB\RecordIsNotFoundException as DBRecordIsNotFoundException;
use function Spatie\SslCertificate\starts_with;

class PdfTableKeyRepository extends BaseRepository implements IPdfTableKeyRepository
{
    /**
     * get corresponding model class name
     * @return string
     */
    function getRepositoryModelClass(): string
    {
        return PdfTableKey::class;
    }

    /**
     * Find record
     * @param $key
     * @return PdfTableKey
     */
    public function findByKey(string $key): PdfTableKey | null
    {
        return PdfTableKey::query()->where('key', $key)->first();
    }

    /**
     * Get api key
     * @return PdfTableKey
     */
    public function getKey(): PdfTableKey | null
    {
        return PdfTableKey::query()->where('amount', '>', 0)->first();
    }
}
