<?php

namespace App\Services;

use App\DataResources\PaginationInfo;
use App\Exceptions\DB\CannotDeleteDBException;
use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\CannotUpdateDBException;
use App\Exceptions\DB\RecordIsNotFoundException;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface IService
{
    /**
     *  apply pagination info on query
     */
    public function applyPagination(Builder $query, PaginationInfo &$pagination = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator;

    /**
     * Get single object of resources
     *
     * @param int $id
     * @param array<string> $withs
     * @return Model
     */
    public function getSingleObject(int $id, array $withs = []): Model;

    /**
     * Search list of items
     * @param array $rawConditions
     * @param PaginationInfo|null $paging
     * @param array<string> $withs
     * @return Collection<int,Model>
     */
    public function search(array $rawConditions, PaginationInfo &$paging = null, array $withs = []): Collection;

    /**
     * Create new item
     *
     * @param array $param
     * @param MetaInfo|null $commandMetaInfo
     * @return Model
     * @throws RecordIsNotFoundException
     * @throws CannotSaveToDBException
     */
    public function create(array $param, MetaInfo $commandMetaInfo = null): Model;

    /**
     * Update new item
     *
     * @param int $catId
     * @param array $param
     * @param MetaInfo|null $commandMetaInfo
     * @return Model
     * @throws RecordIsNotFoundException
     * @throws CannotUpdateDBException
     */
    public function update(int $catId, array $param, MetaInfo $commandMetaInfo = null): Model;

    /**
     * Delete item from resources
     *
     * @param int $id
     * @param bool $softDelete
     * @param MetaInfo|null $commandMetaInfo
     * @throws RecordIsNotFoundException
     * @throws CannotDeleteDBException
     * @return bool
     */
    public function delete(int $id, bool $softDelete = true, MetaInfo $commandMetaInfo = null): bool;

    /**
     * Limit activities of service with role, by default not any limitation is implemented yet.
     * @param UserRoles $role
     * @return mixed
     */
    public function withRole(UserRoles $role);

}
