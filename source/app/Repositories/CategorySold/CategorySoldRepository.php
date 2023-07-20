<?php

namespace App\Repositories\CategorySold;

use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\IdIsNotProvidedException;
use App\Helpers\Common\MetaInfo;
use App\Models\CategorySold;
use App\Repositories\BaseRepository;
use App\Exceptions\DB\RecordIsNotFoundException as DBRecordIsNotFoundException;
use Illuminate\Support\Collection;

use function Spatie\SslCertificate\starts_with;

class CategorySoldRepository extends BaseRepository implements ICategorySoldRepository
{
    /**
     * get corresponding model class name
     * @return string
     */
    function getRepositoryModelClass(): string
    {
        return CategorySold::class;
    }

    /**
     * Get all category solds
     */
    public function getAllCategorySolds(): Collection
    {
        $cat = CategorySold::where('status', 1)->orderBy('id')->get();
        return $cat;
    }
}
