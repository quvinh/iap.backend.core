<?php

namespace App\Repositories\Formula;

use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\CannotUpdateDBException;
use App\Exceptions\DB\IdIsNotProvidedException;
use App\Helpers\Common\MetaInfo;
use App\Models\Formula;
use App\Repositories\BaseRepository;
use App\Exceptions\DB\RecordIsNotFoundException as DBRecordIsNotFoundException;
use App\Models\FormulaCategoryPurchase;
use App\Models\FormulaCategorySold;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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

    /**
     * Get formula_category_sold
     */
    public function getSingleCategorySoldObject(mixed $idFor, mixed $idCat): Builder
    {
        $query = (new FormulaCategorySold())->query();
        return $query->where([
            ['formula_id', $idFor],
            ['category_sold_id', $idCat],
        ]);
    }

    /**
     * Create formula_category_sold
     * @param array $param
     */
    public function createCategorySold(array $param): Model
    {
        $entity = new FormulaCategorySold();
        $entity->formula_id = $param['formula_id'];
        $entity->category_sold_id = $param['category_sold_id'];
        $chk = $entity->save();

        if ($chk) {
            return $entity;
        } else {
            throw new CannotSaveToDBException();
        }
    }

    /**
     * Update formula_category_sold
     * @param array $param
     */
    // public function updateCategorySold(array $param): Model
    // {
    //     if (!in_array('id', array_keys($param))) throw new IdIsNotProvidedException();
    //     $entity = (new FormulaCategorySold())->query()->where('id', $param['id'])->first();
    //     if ($entity === null)
    //         throw new DBRecordIsNotFoundException();
    //     $chk = $entity->save();

    //     if ($chk) {
    //         return $entity;
    //     } else {
    //         throw new CannotUpdateDBException();
    //     }
    // }

    /**
     * Delete id not in ids formula_category_sold
     * @param array $ids
     */
    public function deleteCategorySold(mixed $idFor, array $ids): bool
    {
        $list = (new FormulaCategorySold())->query()->where('formula_id', $idFor)->get(['id', 'category_sold_id'])->toArray();

        $needDelete = array_filter($list, function ($item) use ($ids) {
            return !in_array($item['category_sold_id'], $ids);
        });

        foreach ($needDelete as $item) {
            (new FormulaCategorySold())->query()->where('id', $item['id'])->delete();         
        }
        return true;
    }

    //-----------------------------

    /**
     * Get formula_category_purchase
     */
    public function getSingleCategoryPurchaseObject(mixed $idFor, mixed $idCat): Builder
    {
        $query = (new FormulaCategoryPurchase())->query();
        return $query->where([
            ['formula_id', $idFor],
            ['category_purchase_id', $idCat],
        ]);
    }

    /**
     * Create formula_category_purchase
     * @param array $param
     */
    public function createCategoryPurchase(array $param): Model
    {
        $entity = new FormulaCategoryPurchase();
        $entity->setFormulaCategoryPurchase($param['value_from'], $param['value_to']);
        $entity->formula_id = $param['formula_id'];
        $entity->category_purchase_id = $param['category_purchase_id'];
        $chk = $entity->save();

        if ($chk) {
            return $entity;
        } else {
            throw new CannotSaveToDBException();
        }
    }

    /**
     * Update formula_category_purchase
     * @param array $param
     */
    public function updateCategoryPurchase(array $param): Model
    {
        if (!in_array('id', array_keys($param))) throw new IdIsNotProvidedException();
        $entity = (new FormulaCategoryPurchase())->query()->where('id', $param['id'])->first();
        if ($entity === null)
            throw new DBRecordIsNotFoundException();
        $entity->setFormulaCategoryPurchase($param['value_from'], $param['value_to']);
        $chk = $entity->save();

        if ($chk) {
            return $entity;
        } else {
            throw new CannotUpdateDBException();
        }
    }

    /**
     * Delete id not in ids formula_category_purchase
     * @param array $ids
     */
    public function deleteCategoryPurchase(mixed $idFor, array $ids): bool
    {
        $list = (new FormulaCategoryPurchase())->query()->where('formula_id', $idFor)->get(['id', 'category_purchase_id'])->toArray();
        
        $needDelete = array_filter($list, function ($item) use ($ids) {
            return !in_array($item['category_purchase_id'], $ids);
        });

        foreach ($needDelete as $item) {
            (new FormulaCategoryPurchase())->query()->where('id', $item['id'])->delete();         
        }
        return true;
    }
}
