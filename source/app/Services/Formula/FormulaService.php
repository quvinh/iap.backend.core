<?php

namespace App\Services\Formula;

use App\DataResources\PaginationInfo;
use App\DataResources\SortInfo;
use App\Exceptions\Business\ActionFailException;
use App\Exceptions\DB\CannotDeleteDBException;
use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\CannotUpdateDBException;
use App\Exceptions\DB\RecordIsNotFoundException;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Utils\StorageHelper;
use App\Helpers\Utils\StringHelper;
use App\Models\Formula;
use App\Repositories\CategoryPurchase\ICategoryPurchaseRepository;
use App\Repositories\CategorySold\ICategorySoldRepository;
use App\Repositories\Formula\IFormulaRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class FormulaService extends \App\Services\BaseService implements IFormulaService
{
    private ?IFormulaRepository $formulaRepos = null;
    private ?ICategorySoldRepository $catSoldRepos = null;
    private ?ICategoryPurchaseRepository $catPurchaseRepos = null;

    public function __construct(IFormulaRepository $repos, ICategorySoldRepository $catSoldRepos, ICategoryPurchaseRepository $catPurchaseRepos)
    {
        $this->formulaRepos = $repos;
        $this->catSoldRepos = $catSoldRepos;
        $this->catPurchaseRepos = $catPurchaseRepos;
    }

    /**
     * Get single object of resources
     *
     * @param int $id
     * @param array<string> $withs
     * @return Formula
     * @throws RecordIsNotFoundException
     */
    public function getSingleObject(int $id, array $withs = []): Formula
    {
        try {
            $query = $this->formulaRepos->queryOnAField(['id', $id]);
            $query = $this->formulaRepos->with($withs, $query);
            $record = $query->first();
            if (!is_null($record)) return $record;
            throw new RecordIsNotFoundException();
        } catch (Exception $e) {
            throw new RecordIsNotFoundException(
                message: 'get single object: ' . json_encode(['id' => $id, 'withs' => $withs]),
                previous: $e
            );
        }
    }

    /**
     * Search list of items
     *
     * @param array<string> $rawConditions
     * @param PaginationInfo|null $paging
     * @param array<string> $withs
     * @return Collection<int,Formula>
     * @throws ActionFailException
     * @throws Throwable
     */
    public function search(array $rawConditions, PaginationInfo &$paging = null, array $withs = []): Collection
    {
        try {
            $query = $this->formulaRepos->search();
            if (isset($rawConditions['name'])) {
                $param = StringHelper::escapeLikeQueryParameter($rawConditions['name']);
                $query = $this->formulaRepos->queryOnAField([DB::raw("upper(name)"), 'LIKE BINARY', DB::raw("upper(concat('%', ? , '%'))")], positionalBindings: ['name' => $param]);
            }

            if (isset($rawConditions['company_detail_id'])) {
                $param = $rawConditions['company_detail_id'];
                $query = $this->formulaRepos->queryOnAField(['company_detail_id', '=', $param], $query);
            }

            if (isset($rawConditions['id'])) {
                $param = $rawConditions['id'];
                $query = $this->formulaRepos->queryOnAField(['id', '=', $param], $query);
            }

            if (isset($rawConditions['updated_date'])) {
                $query = $this->formulaRepos->queryOnDateRangeField($query, 'updated_at', $rawConditions['updated_date']);
            }
            if (isset($rawConditions['created_date'])) {
                $query = $this->formulaRepos->queryOnDateRangeField($query, 'created_at', $rawConditions['created_date']);
            }

            $query = $this->formulaRepos->with($withs, $query);


            if (!is_null($paging)) {
                $this->applyPagination($query, $paging);
            }

            if (isset($rawConditions['sort'])) {
                $sort = SortInfo::parse($rawConditions['sort']);
                return $this->formulaRepos->sort($query, $sort)->get();
            }
            return $query->get();
        } catch (Exception $e) {
            throw new ActionFailException(
                message: 'search: ' . json_encode(['conditions' => $rawConditions, 'paging' => $paging, 'withs' => $withs]),
                previous: $e
            );
        }
    }

    /**
     * Create new item
     *
     * @param array $param
     * @param MetaInfo|null $commandMetaInfo
     * @return Formula
     * @throws CannotSaveToDBException
     */
    public function create(array $param, MetaInfo $commandMetaInfo = null): Formula
    {
        DB::beginTransaction();
        try {
            #1 Create
            $record = $this->formulaRepos->create($param, $commandMetaInfo);
            DB::commit();
            #2 Return
            return $record;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new CannotSaveToDBException(
                message: 'create: ' . json_encode(['param' => $param]),
                previous: $e
            );
        }
    }

    /**
     * @param int $id
     * @param array $param
     * @param MetaInfo|null $commandMetaInfo
     * @return Formula
     * @throws CannotUpdateDBException
     */
    public function update(int $id, array $param, MetaInfo $commandMetaInfo = null): Formula
    {
        DB::beginTransaction();
        try {
            #1: Can edit? -> Yes: move to #2 No: return Exception with error
            $record = $this->formulaRepos->getSingleObject($id)->first();
            if (empty($record)) {
                throw new RecordIsNotFoundException();
            }
            #2: update
            $param = array_merge($param, [
                'id' => $record->id
            ]);
            $record = $this->formulaRepos->update($param, $commandMetaInfo);
            // update picture if needed
            // code here
            DB::commit();
            return $record;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new CannotUpdateDBException(
                message: 'update: ' . $e->getMessage(),
                previous: $e
            );
        }
    }

    /**
     * Delete item from resources
     *
     * @param int $id
     * @param bool $softDelete
     * @param MetaInfo|null $commandMetaInfo
     * @return bool
     * @throws CannotDeleteDBException
     */
    public function delete(int $id, bool $softDelete = true, MetaInfo $commandMetaInfo = null): bool
    {
        DB::beginTransaction();
        try {
            $record = $this->formulaRepos->getSingleObject($id)->first();
            if (empty($record)) {
                throw new RecordIsNotFoundException();
            }
            $result =  $this->formulaRepos->delete(id: $id, soft: $softDelete, meta: $commandMetaInfo);
            DB::commit();
            return $result;
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new CannotDeleteDBException(
                message: 'update: ' . json_encode(['id' => $id, 'softDelete' => $softDelete]),
                previous: $ex
            );
        }
    }

    /**
     * Update formula detail
     * @param array $param
     * @param mixed $id
     */
    public function updateDetail(mixed $id, array $param, MetaInfo $commandMetaInfo = null): Formula
    {
        DB::beginTransaction();
        try {
            $entity = $this->formulaRepos->getSingleObject($id)->first();
            if (!$entity) throw new RecordIsNotFoundException();
            # TODO: category solds
            $this->formulaRepos->deleteCategorySold($entity->id, $param['category_solds']);
            foreach ($param['category_solds'] as $idCat) {
                $cat = $this->catSoldRepos->getSingleObject($idCat)->first();
                if (!$cat) throw new RecordIsNotFoundException();
                $ckCat = $this->formulaRepos->getSingleCategorySoldObject($entity->id, $cat->id)->first();
                if (!$ckCat) {
                    $this->formulaRepos->createCategorySold([
                        'formula_id' => $entity->id,
                        'category_sold_id' => $cat->id,
                    ]);
                }
            }
            
            # TODO: category purchases
            $this->formulaRepos->deleteCategoryPurchase($entity->id, array_map(function ($value) {
                return $value['id'];
            }, $param['category_purchases']));
            $sum_from = 0;
            $sum_to = 0;
            foreach ($param['category_purchases'] as $category) {
                $cat = $this->catPurchaseRepos->getSingleObject($category['id'])->first();
                if (!$cat) throw new RecordIsNotFoundException();
                $ckCat = $this->formulaRepos->getSingleCategoryPurchaseObject($entity->id, $cat->id)->first();
                if ($ckCat) {
                    # Update info
                    $this->formulaRepos->updateCategoryPurchase([
                        'id' => $ckCat->id,
                        'formula_id' => $entity->id,
                        'category_purchase_id' => $ckCat->id,
                        'value_from' => $category['value_from'],
                        'value_to' => $category['value_to'],
                    ]);

                    # Compute sum value formula
                    $sum_from += $category['value_from'] ?? 0;
                    $sum_to += $category['value_to'] ?? 0;
                } else {
                    # Create info
                    $this->formulaRepos->createCategoryPurchase([
                        'formula_id' => $entity->id,
                        'category_purchase_id' => $cat->id,
                        'value_from' => $category['value_from'],
                        'value_to' => $category['value_to'],
                    ]);

                    # Compute sum value formula
                    $sum_from += $category['value_from'] ?? 0;
                    $sum_to += $category['value_to'] ?? 0;
                }
            }

            // $record = $this->formulaRepos->update([
            //     'id' => $entity->id,
            //     'company_detail_id' => $param['company_detail_id'],
            //     'company_type_id' => $param['company_type_id'],
            //     'name' => $param['name'],
            //     'note' => $param['note'] ?? null,
            // ], $commandMetaInfo);
            $entity->company_detail_id = $param['company_detail_id'];
            $entity->company_type_id = $param['company_type_id'];
            $entity->name = $param['name'];
            $entity->note = $param['note'] ?? null;
            $entity->setFormula($sum_from, $sum_to);
            if (!$entity->save()) throw new ActionFailException();
            
            DB::commit();
            return $entity;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ActionFailException(
                message: 'action failure',
                previous: $e
            );
        }
    }
}
