<?php

namespace App\Services;

use App\DataResources\PaginationInfo;
use App\Exceptions\NotImplementedException;
use App\Exceptions\Request\InvalidDatetimeInputException;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Helpers\Utils\DateHelper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class BaseService implements IService
{
    protected ?UserRoles $role = null;
    /**
     *  apply pagination info on query
     * @param Builder $query
     * @param PaginationInfo|null $pagination
     * @return LengthAwarePaginator
     */
    public function applyPagination(Builder $query, PaginationInfo &$pagination = null): LengthAwarePaginator
    {
        $paginator = $query->paginate(perPage:$pagination->perPage, page: $pagination->page);
        $pagination->total = $paginator->total();
        $pagination->lastPage = $paginator->lastPage();
        return $paginator;
    }

    /**
     * @throws NotImplementedException
     */
    public function getSingleObject(int $id, array $withs = []): Model
    {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    public function search(array $rawConditions, PaginationInfo &$paging = null, array $withs = []): Collection
    {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    public function create(array $param, MetaInfo $commandMetaInfo = null): Model
    {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    public function update(int $catId, array $param, MetaInfo $commandMetaInfo = null): Model
    {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    public function delete(int $id, bool $softDelete = true, MetaInfo $commandMetaInfo = null): bool
    {
        throw new NotImplementedException();
    }

    /**
     * Limit activities of service with role, by default not any limitation is implemented yet.
     * @param UserRoles $role
     * @return mixed
     */
    public function withRole(UserRoles $role){
        $this->role = $role;
    }

}
