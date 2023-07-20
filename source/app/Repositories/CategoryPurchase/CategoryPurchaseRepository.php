<?php

namespace App\Repositories\CategoryPurchase;

use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\IdIsNotProvidedException;
use App\Helpers\Common\MetaInfo;
use App\Models\CategoryPurchase;
use App\Repositories\BaseRepository;
use App\Exceptions\DB\RecordIsNotFoundException as DBRecordIsNotFoundException;
use Illuminate\Support\Collection;

use function Spatie\SslCertificate\starts_with;

class CategoryPurchaseRepository extends BaseRepository implements ICategoryPurchaseRepository
{
    /**
     * get corresponding model class name
     * @return string
     */
    function getRepositoryModelClass(): string
    {
        return CategoryPurchase::class;
    }

    /**
     * Get all category purchases
     */
    public function getAllCategoryPurchases(): Collection
    {
        $cat = CategoryPurchase::where('status', 1)->orderBy('id')->get();
        return $cat;
    }
}
