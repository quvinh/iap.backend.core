<?php

namespace App\Services\InvoiceMedia;

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
use App\Models\InvoiceMedia;
use App\Repositories\InvoiceMedia\IInvoiceMediaRepository;
use App\Services\User\IUserService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class InvoiceMediaService extends \App\Services\BaseService implements IInvoiceMediaService
{
    private ?IInvoiceMediaRepository $invoiceMediaRepos = null;
    private ?IUserService $userService = null;

    public function __construct(IInvoiceMediaRepository $repos, IUserService $userService)
    {
        $this->invoiceMediaRepos = $repos;
        $this->userService = $userService;
    }

    /**
     * Get single object of resources
     *
     * @param int $id
     * @param array<string> $withs
     * @return InvoiceMedia
     * @throws RecordIsNotFoundException
     */
    public function getSingleObject(int $id, array $withs = []): InvoiceMedia
    {
        try {
            $query = $this->invoiceMediaRepos->queryOnAField(['id', $id]);
            $query = $this->invoiceMediaRepos->with($withs, $query);
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
     * @param array $rawConditions
     * @param PaginationInfo|null $paging
     * @param array<string> $withs
     * @return Collection<int,InvoiceMedia>
     * @throws ActionFailException
     * @throws Throwable
     */
    public function search(array $rawConditions, PaginationInfo &$paging = null, array $withs = []): Collection
    {
        try {
            $query = $this->invoiceMediaRepos->search();

            if (isset($rawConditions['name'])) {
                $param = StringHelper::escapeLikeQueryParameter($rawConditions['name']);
                $query = $this->invoiceMediaRepos->queryOnAField([DB::raw("upper(name)"), 'LIKE BINARY', DB::raw("upper(concat('%', ? , '%'))")], positionalBindings: ['name' => $param]);
            }

            if (isset($rawConditions['company_id'])) {
                $param = $rawConditions['company_id'];
                $query = $this->invoiceMediaRepos->queryOnAField(['company_id', '=', $param], $query);
            }

            if (isset($rawConditions['year'])) {
                $param = $rawConditions['year'];
                $query = $this->invoiceMediaRepos->queryOnAField(['year', '=', $param], $query);
            }

            if (isset($rawConditions['status'])) {
                $param = $rawConditions['status'];
                $query = $this->invoiceMediaRepos->queryOnAField(['status', '=', $param], $query);
            }

            if (isset($rawConditions['updated_date'])) {
                $query = $this->invoiceMediaRepos->queryOnDateRangeField($query, 'updated_at', $rawConditions['updated_date']);
            }
            if (isset($rawConditions['created_date'])) {
                $query = $this->invoiceMediaRepos->queryOnDateRangeField($query, 'created_at', $rawConditions['created_date']);
            }

            # Query get companies authoritied
            $userId = auth()->user()->getAuthIdentifier();
            $userCompanies = $this->userService->findByCompanies($userId);
            if (empty($userCompanies)) {
                $query->whereIn('company_id', []);
            } else {
                $arr = array_map(function ($item) {
                    return $item['company_id'];
                }, $userCompanies);
                $query->whereIn('company_id', $arr);
            }

            $query = $this->invoiceMediaRepos->with($withs, $query);


            if (!is_null($paging)) {
                $this->applyPagination($query, $paging);
            }

            if (isset($rawConditions['sort'])) {
                $sort = SortInfo::parse($rawConditions['sort']);
                return $this->invoiceMediaRepos->sort($query, $sort)->get();
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
     * @return InvoiceMedia
     * @throws CannotSaveToDBException
     */
    public function create(array $param, MetaInfo $commandMetaInfo = null): InvoiceMedia
    {
        DB::beginTransaction();
        try {
            #1 Create
            $record = $this->invoiceMediaRepos->create($param, $commandMetaInfo);
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
     * @return InvoiceMedia
     * @throws CannotUpdateDBException
     */
    public function update(int $id, array $param, MetaInfo $commandMetaInfo = null): InvoiceMedia
    {
        DB::beginTransaction();
        try {
            #1: Can edit? -> Yes: move to #2 No: return Exception with error
            $record = $this->invoiceMediaRepos->getSingleObject($id)->first();
            if (empty($record)) {
                throw new RecordIsNotFoundException();
            }
            #2: update
            $param = array_merge($param, [
                'id' => $record->id
            ]);
            $record = $this->invoiceMediaRepos->update($param, $commandMetaInfo);
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
            $record = $this->invoiceMediaRepos->getSingleObject($id)->first();
            if (empty($record)) {
                throw new RecordIsNotFoundException();
            }
            $result =  $this->invoiceMediaRepos->delete(id: $id, soft: $softDelete, meta: $commandMetaInfo);
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
