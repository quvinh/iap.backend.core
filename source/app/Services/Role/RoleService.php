<?php

namespace App\Services\Role;

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
use App\Models\PermissionGroup;
use App\Models\Role;
use App\Repositories\Role\IRoleRepository;
use App\Services\Permission\IPermissionService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class RoleService extends \App\Services\BaseService implements IRoleService
{
    private ?IRoleRepository $roleRepos = null;
    private ?IPermissionService $permissionService = null;

    public function __construct(IRoleRepository $repos, IPermissionService $permissionService)
    {
        $this->roleRepos = $repos;
        $this->permissionService = $permissionService;
    }

    /**
     * Get single object of resources
     *
     * @param int $id
     * @param array<string> $withs
     * @return Role
     * @throws RecordIsNotFoundException
     */
    public function getSingleObject(int $id, array $withs = []): Role
    {
        try {
            $query = $this->roleRepos->queryOnAField(['id', $id]);
            $query = $this->roleRepos->with($withs, $query);
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
     * @return Collection<int,Role>
     * @throws ActionFailException
     * @throws Throwable
     */
    public function search(array $rawConditions, PaginationInfo &$paging = null, array $withs = []): Collection
    {
        try {
            $query = $this->roleRepos->search();
            if (isset($rawConditions['name'])) {
                $param = StringHelper::escapeLikeQueryParameter($rawConditions['name']);
                $query = $this->roleRepos->queryOnAField([DB::raw("upper(name)"), 'LIKE BINARY', DB::raw("upper(concat('%', ? , '%'))")], positionalBindings: ['name' => $param]);
            }

            if (isset($rawConditions['updated_date'])) {
                $query = $this->roleRepos->queryOnDateRangeField($query, 'updated_at', $rawConditions['updated_date']);
            }
            if (isset($rawConditions['created_date'])) {
                $query = $this->roleRepos->queryOnDateRangeField($query, 'created_at', $rawConditions['created_date']);
            }

            $query = $this->roleRepos->with($withs, $query);


            if (!is_null($paging)) {
                $this->applyPagination($query, $paging);
            }

            if (isset($rawConditions['sort'])) {
                $sort = SortInfo::parse($rawConditions['sort']);
                return $this->roleRepos->sort($query, $sort)->get();
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
     * @return Role
     * @throws CannotSaveToDBException
     */
    public function create(array $param, MetaInfo $commandMetaInfo = null): Role
    {
        DB::beginTransaction();
        try {
            #1 Create
            $record = $this->roleRepos->create($param, $commandMetaInfo);

            if (!empty($param['permissions'])) {
                foreach ($param['permissions'] as $slug) {
                    $permission = $this->permissionService->findBySlug($slug);
                    if (empty($permission)) continue;
                    $premissionGroup = new PermissionGroup();
                    $premissionGroup->role_id = $record->id;
                    $premissionGroup->permission_id = $permission->id;
                    if (!$premissionGroup->save()) throw new CannotSaveToDBException(message: "CannotSaveToDBException permission_group not saved in update service");
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
     * @return Role
     * @throws CannotUpdateDBException
     */
    public function update(int $id, array $param, MetaInfo $commandMetaInfo = null): Role
    {
        DB::beginTransaction();
        try {
            #1: Can edit? -> Yes: move to #2 No: return Exception with error
            $record = $this->roleRepos->getSingleObject($id)->first();
            if (empty($record)) throw new RecordIsNotFoundException();
            #2: update
            $param = array_merge($param, [
                'id' => $record->id
            ]);
            $record = $this->roleRepos->update($param, $commandMetaInfo);

            # Delete permission_group before updated
            PermissionGroup::query()->where('role_id', $record->id)->delete();
            if (!empty($param['permissions'])) {
                foreach ($param['permissions'] as $slug) {
                    $permission = $this->permissionService->findBySlug($slug);
                    if (empty($permission)) continue;
                    $premissionGroup = new PermissionGroup();
                    $premissionGroup->role_id = $record->id;
                    $premissionGroup->permission_id = $permission->id;
                    if (!$premissionGroup->save()) throw new CannotSaveToDBException(message: "CannotSaveToDBException permission_group not saved in update service");
                }
            }
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
            $record = $this->roleRepos->getSingleObject($id)->first();
            if (empty($record)) throw new RecordIsNotFoundException();
            PermissionGroup::query()->where('role_id', $record->id)->delete();
            $result =  $this->roleRepos->delete(id: $id, soft: $softDelete, meta: $commandMetaInfo);
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
