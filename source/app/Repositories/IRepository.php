<?php

namespace App\Repositories;

use App\DataResources\IDataResource;
use App\DataResources\SortInfo;
use App\Exceptions\Request\InvalidDatetimeInputException;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Filters\BasicFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;

interface IRepository
{
    /**
     * get corresponding model class name
     * @return string
     */
    function getRepositoryModelClass(): string;

    /**
     * Search model items using a given basic filter (context filter) or return a query builder
     * @param BasicFilter $filter
     * @param bool $onlyActive
     * @return Builder
     */
    function search(BasicFilter $filter, bool $onlyActive = true, array $withs = []): Builder;

    /**
     * Find a single object base on its id
     * @param mixed $id
     * @param string $idColumnName
     * @return mixed
     */
    function getSingleObject(mixed $id, string $idColumnName = 'id', array $withs = []): Builder | null;

    /**
     * Try to create the object using the given info
     * @param array<string, mixed> $form
     * @param MetaInfo|null $meta
     * @return mixed
     */
    function create(array $form, MetaInfo $meta = null, string $idColumnName = 'id'): Model;

    /**
     * Try to save the object using the given info
     * @param array<mixed> $form
     * @param MetaInfo|null $meta
     * @param string $idColumnName
     * @return mixed
     */
    function update(array $form, MetaInfo $meta = null, string $idColumnName = 'id'): Model;

    /**
     * Try to delete a model based on its id
     * @param mixed $id
     * @param bool $soft
     * @param MetaInfo|null $meta
     * @return bool
     */
    function delete(mixed $id, bool $soft = false, MetaInfo $meta = null, string $idColumnName = 'id'): bool;

    /**
     * Try to restore a model record
     * @param mixed $id
     * @param bool $soft
     * @param MetaInfo|null $meta
     * @param string $idColumnName
     * @return bool
     */
    function restore(mixed $id, bool $soft = false, MetaInfo $meta = null, string $idColumnName = 'id'): bool;

    /**
     * Try to count matched items based on the given filter
     * @param BasicFilter|null $filter
     * @param bool $onlyActive
     * @return int
     */
    function count(BasicFilter $filter = null, bool $onlyActive = true): int;

    /**
     * Query by applying a filter condition on a field name
     * @param array<mixed>|null $condition
     * @param Builder|null $query
     * @param array<mixed>|null $positionalBindings
     * @return Builder
     */
    function queryOnAField(array $condition = null, Builder $query = null, array $positionalBindings = null): Builder;


    /**
     * Apply search on created_at or updated_at
     * @param Builder $query
     * @param string $fieldName
     * @param array $rawConditions
     * @return Builder
     * @throws InvalidDatetimeInputException
     */
    public function queryOnDateRangeField(Builder $query, string $fieldName, array $rawConditions = []): Builder;

    /**
     * Add extra relationship field to query
     * @param array<string> $withs
     * @param Builder|null $query
     * @return Builder
     */
    function with(array $withs, Builder $query = null): Builder;

    /**
     *  apply sort info on query
     * @param Builder $query
     * @param SortInfo|null $sort
     * @return Builder
     */
    public function sort(Builder $query, SortInfo &$sort = null): Builder;
}
