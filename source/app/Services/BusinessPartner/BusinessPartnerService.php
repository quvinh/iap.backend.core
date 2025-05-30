<?php

namespace App\Services\BusinessPartner;

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
use App\Models\BusinessPartner;
use App\Repositories\BusinessPartner\IBusinessPartnerRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class BusinessPartnerService extends \App\Services\BaseService implements IBusinessPartnerService
{
    private ?IBusinessPartnerRepository $businessPartnerRepos = null;

    public function __construct(IBusinessPartnerRepository $repos)
    {
        $this->businessPartnerRepos = $repos;
    }

    /**
     * Get single object of resources
     *
     * @param int $id
     * @param array<string> $withs
     * @return BusinessPartner
     * @throws RecordIsNotFoundException
     */
    public function getSingleObject(int $id, array $withs = []): BusinessPartner
    {
        try {
            $query = $this->businessPartnerRepos->queryOnAField(['id', $id]);
            $query = $this->businessPartnerRepos->with($withs, $query);
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
     * @return Collection<int,BusinessPartner>
     * @throws ActionFailException
     * @throws Throwable
     */
    public function search(array $rawConditions, PaginationInfo &$paging = null, array $withs = []): Collection
    {
        try {
            $query = $this->businessPartnerRepos->search();
            
            if (isset($rawConditions['tax_code'])) {
                $param = $rawConditions['tax_code'];
                $query = $this->businessPartnerRepos->queryOnAField(['tax_code', '=', $param], $query);
            }

            $query = $this->businessPartnerRepos->with($withs, $query);


            if (!is_null($paging)) {
                $this->applyPagination($query, $paging);
            }

            if (isset($rawConditions['sort'])) {
                $sort = SortInfo::parse($rawConditions['sort']);
                return $this->businessPartnerRepos->sort($query, $sort)->get();
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
     * @return BusinessPartner
     * @throws CannotSaveToDBException
     */
    public function create(array $param, MetaInfo $commandMetaInfo = null): BusinessPartner
    {
        DB::beginTransaction();
        try {
            #1 Create
            $record = $this->businessPartnerRepos->create($param, $commandMetaInfo);
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
     * @return BusinessPartner
     * @throws CannotUpdateDBException
     */
    public function update(int $id, array $param, MetaInfo $commandMetaInfo = null): BusinessPartner
    {
        DB::beginTransaction();
        try {
            #1: Can edit? -> Yes: move to #2 No: return Exception with error
            $record = $this->businessPartnerRepos->getSingleObject($id)->first();
            if (empty($record)) {
                throw new RecordIsNotFoundException();
            }
            #2: update
            $param = array_merge($param, [
                'id' => $record->id
            ]);
            $record = $this->businessPartnerRepos->update($param, $commandMetaInfo);
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
            $record = $this->businessPartnerRepos->getSingleObject($id)->first();
            if (empty($record)) {
                throw new RecordIsNotFoundException();
            }
            $result =  $this->businessPartnerRepos->delete(id: $id, soft: $softDelete, meta: $commandMetaInfo);
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

    public function find(array $params): mixed
    {
        return $this->businessPartnerRepos->find($params);
    }
}
