<?php

namespace App\Services\ItemGroup;

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
use App\Models\ItemGroup;
use App\Repositories\ItemCode\IItemCodeRepository;
use App\Repositories\ItemGroup\IItemGroupRepository;
use App\Services\User\IUserService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class ItemGroupService extends \App\Services\BaseService implements IItemGroupService
{
    private ?IItemGroupRepository $itemGroupRepos = null;
    private ?IItemCodeRepository $itemCodeRepos = null;
    private ?IUserService $userService = null;

    public function __construct(IItemGroupRepository $repos, IItemCodeRepository $itemCodeRepos, IUserService $userService)
    {
        $this->itemGroupRepos = $repos;
        $this->itemCodeRepos = $itemCodeRepos;
        $this->userService = $userService;
    }

    /**
     * Get single object of resources
     *
     * @param int $id
     * @param array<string> $withs
     * @return ItemGroup
     * @throws RecordIsNotFoundException
     */
    public function getSingleObject(int $id, array $withs = []): ItemGroup
    {
        try {
            $query = $this->itemGroupRepos->queryOnAField(['id', $id]);
            $query = $this->itemGroupRepos->with($withs, $query);
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
     * @return Collection<int,ItemGroup>
     * @throws ActionFailException
     * @throws Throwable
     */
    public function search(array $rawConditions, PaginationInfo &$paging = null, array $withs = []): Collection
    {
        try {
            $query = $this->itemGroupRepos->search();

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

            if (isset($rawConditions['name'])) {
                $param = StringHelper::escapeLikeQueryParameter($rawConditions['name']);
                $query = $this->itemGroupRepos->queryOnAField([DB::raw("upper(name)"), 'LIKE BINARY', DB::raw("upper(concat('%', ? , '%'))")], positionalBindings: ['name' => $param]);
            }

            if (isset($rawConditions['code'])) {
                $param = $rawConditions['code'];
                $query = $this->itemGroupRepos->queryOnAField(['code', 'LIKE', "%$param%"], $query);
            }

            if (isset($rawConditions['year'])) {
                $param = $rawConditions['year'];
                $query = $this->itemGroupRepos->queryOnAField(['year', '=', $param], $query);
            }

            if (isset($rawConditions['company_id'])) {
                $param = $rawConditions['company_id'];
                $query = $this->itemGroupRepos->queryOnAField(['company_id', '=', $param], $query);
            }

            if (isset($rawConditions['updated_date'])) {
                $query = $this->itemGroupRepos->queryOnDateRangeField($query, 'updated_at', $rawConditions['updated_date']);
            }
            if (isset($rawConditions['created_date'])) {
                $query = $this->itemGroupRepos->queryOnDateRangeField($query, 'created_at', $rawConditions['created_date']);
            }

            $query = $this->itemGroupRepos->with($withs, $query);


            if (!is_null($paging)) {
                $this->applyPagination($query, $paging);
            }

            if (isset($rawConditions['sort'])) {
                $sort = SortInfo::parse($rawConditions['sort']);
                return $this->itemGroupRepos->sort($query, $sort)->get();
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
     * @return ItemGroup
     * @throws CannotSaveToDBException
     */
    public function create(array $param, MetaInfo $commandMetaInfo = null): ItemGroup
    {
        DB::beginTransaction();
        try {
            #1 Create
            $record = $this->itemGroupRepos->create($param, $commandMetaInfo);
            if (!empty($param['item_codes'])) {
                foreach ($param['item_codes'] as $index => $item) {
                    $row = $this->itemCodeRepos->getSingleObject($item['id'])->first();
                    if (empty($row)) throw new RecordsNotFoundException();
                    if ($index == 0) {
                        $record->year = $row->year;
                        $record->save();
                    }
                    $row->item_group_id = $record->id;
                    if (!$row->save()) throw new CannotSaveToDBException();
                }
            }
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
     * @return ItemGroup
     * @throws CannotUpdateDBException
     */
    public function update(int $id, array $param, MetaInfo $commandMetaInfo = null): ItemGroup
    {
        DB::beginTransaction();
        try {
            #1: Can edit? -> Yes: move to #2 No: return Exception with error
            $record = $this->itemGroupRepos->getSingleObject($id)->first();
            // dd($record->id,$this->itemCodeRepos->findByGroup($record->id)->get());
            if (empty($record)) {
                throw new RecordIsNotFoundException();
            }
            #2: update
            $param = array_merge($param, [
                'id' => $record->id
            ]);
            $record = $this->itemGroupRepos->update($param, $commandMetaInfo);
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
            $record = $this->itemGroupRepos->getSingleObject($id)->first();
            if (empty($record)) {
                throw new RecordIsNotFoundException();
            }
            $result =  $this->itemGroupRepos->delete(id: $id, soft: $softDelete, meta: $commandMetaInfo);
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
     * Insert into group
     *
     * @param array $param
     * @param MetaInfo|null $commandMetaInfo
     * @return mixed
     * @throws CannotSaveToDBException
     */
    public function insert(array $param): mixed
    {
        DB::beginTransaction();
        try {
            #1 Find
            $record = $this->itemGroupRepos->getSingleObject($param['item_group_id'])->first();
            if (empty($record)) {
                throw new RecordIsNotFoundException();
            }
            if (!empty($param['item_codes'])) {
                foreach ($param['item_codes'] as $index => $item) {
                    $row = $this->itemCodeRepos->getSingleObject($item['id'])->first();
                    if (empty($row)) throw new RecordsNotFoundException();
                    $row->item_group_id = $record->id;
                    if (!$row->save()) throw new CannotSaveToDBException();
                }
            }
            DB::commit();
            #2 Return
            return $record;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new CannotSaveToDBException(
                message: 'insert: ' . json_encode(['param' => $param]),
                previous: $e
            );
        }
    }
}
