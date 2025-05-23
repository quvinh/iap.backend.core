<?php

namespace App\Services\CategoryPurchase;

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
use App\Models\CategoryPurchase;
use App\Repositories\CategoryPurchase\ICategoryPurchaseRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class CategoryPurchaseService extends \App\Services\BaseService implements ICategoryPurchaseService
{
    private ?ICategoryPurchaseRepository $categoryPurchaseRepos = null;

    public function __construct(ICategoryPurchaseRepository $repos)
    {
        $this->categoryPurchaseRepos = $repos;
    }

    /**
     * Get single object of resources
     *
     * @param int $id
     * @param array<string> $withs
     * @return CategoryPurchase
     * @throws RecordIsNotFoundException
     */
    public function getSingleObject(int $id, array $withs = []): CategoryPurchase
    {
        try {
            $query = $this->categoryPurchaseRepos->queryOnAField(['id', $id]);
            $query = $this->categoryPurchaseRepos->with($withs, $query);
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
     * @return Collection<int,CategoryPurchase>
     * @throws ActionFailException
     * @throws Throwable
     */
    public function search(array $rawConditions, PaginationInfo &$paging = null, array $withs = []): Collection
    {
        try {
            $query = $this->categoryPurchaseRepos->search();
            if (isset($rawConditions['name'])) {
                $param = StringHelper::escapeLikeQueryParameter($rawConditions['name']);
                $query = $this->categoryPurchaseRepos->queryOnAField([DB::raw("upper(name)"), 'LIKE BINARY', DB::raw("upper(concat('%', ? , '%'))")], positionalBindings: ['name' => $param]);
            }

            if (isset($rawConditions['updated_date'])) {
                $query = $this->categoryPurchaseRepos->queryOnDateRangeField($query, 'updated_at', $rawConditions['updated_date']);
            }
            if (isset($rawConditions['created_date'])) {
                $query = $this->categoryPurchaseRepos->queryOnDateRangeField($query, 'created_at', $rawConditions['created_date']);
            }

            $query = $this->categoryPurchaseRepos->with($withs, $query);


            if (!is_null($paging)) {
                $this->applyPagination($query, $paging);
            }

            if (isset($rawConditions['sort'])) {
                $sort = SortInfo::parse($rawConditions['sort']);
                return $this->categoryPurchaseRepos->sort($query, $sort)->get();
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
     * Get all category purchases
     * @return Collection
     */
    public function getAllCategoryPurchases(): Collection
    {
        try {
            $cat = $this->categoryPurchaseRepos->getAllCategoryPurchases();
            return $cat;
        } catch (\Exception $e) {
            throw new ActionFailException(
                previous: $e
            );
        }
    }

    /**
     * Create new item
     *
     * @param array $param
     * @param MetaInfo|null $commandMetaInfo
     * @return CategoryPurchase
     * @throws CannotSaveToDBException
     */
    public function create(array $param, MetaInfo $commandMetaInfo = null): CategoryPurchase
    {
        DB::beginTransaction();
        try {
            #1 Create
            $record = $this->categoryPurchaseRepos->create($param, $commandMetaInfo);
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
     * @return CategoryPurchase
     * @throws CannotUpdateDBException
     */
    public function update(int $id, array $param, MetaInfo $commandMetaInfo = null): CategoryPurchase
    {
        DB::beginTransaction();
        try {
            #1: Can edit? -> Yes: move to #2 No: return Exception with error
            $record = $this->categoryPurchaseRepos->getSingleObject($id)->first();
            if (empty($record)) {
                throw new RecordIsNotFoundException();
            }
            #2: update
            $param = array_merge($param, [
                'id' => $record->id
            ]);
            $record = $this->categoryPurchaseRepos->update($param, $commandMetaInfo);
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
            $record = $this->categoryPurchaseRepos->getSingleObject($id)->first();
            if (empty($record)) {
                throw new RecordIsNotFoundException();
            }
            $result =  $this->categoryPurchaseRepos->delete(id: $id, soft: $softDelete, meta: $commandMetaInfo);
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
}
