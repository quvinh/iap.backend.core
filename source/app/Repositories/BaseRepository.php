<?php

namespace App\Repositories;

use App\DataResources\SortInfo;
use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\IdIsNotProvidedException;
use App\Exceptions\Request\InvalidDatetimeInputException;
use App\Exceptions\Request\RequestDataIsInvalidException;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Filters\BasicFilter;
use App\Helpers\Utils\DateHelper;
use Illuminate\Database\Eloquent\Builder;
use App\Exceptions\DB\RecordIsNotFoundException;
use App\Exceptions\DB\RecordIsNotFoundException as DBRecordIsNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

abstract class BaseRepository implements IRepository
{

    /**
     * @param BasicFilter $filter
     * @param Builder|null $query
     * @return Builder
     */
    protected function filter(BasicFilter $filter, Builder $query = null): Builder
    {
        if (is_null($query)) {
            $model_class = $this->getRepositoryModelClass();
            $query = (new $model_class())->query();
        }

        if ($filter->conditions && count($filter->conditions) > 0) {
            foreach ($filter->conditions as $value) {
                $query = $this->queryOnAField($value, $query);
            }
        }
        // ------------------------
        if ($filter->skip != null && $filter->skip > 0)
            $query = $query->skip($filter->skip);
        if ($filter->limit != null && $filter->limit > 0)
            $query = $query->take($filter->limit);
        if ($filter->orders) {
            $keys = array_keys($filter->orders);
            foreach ($filter->orders as $key => $value) {
                $query = $query->orderBy($value[0], $value[1]);
            }
        }
        return $query;
    }

    /**
     * Search model items using a given basic filter (context filter) or return a query builder
     * @param BasicFilter|null $filter
     * @param bool $onlyActive
     * @return Builder
     */
    function search(BasicFilter $filter = null, bool $onlyActive = true, array $withs = []): Builder
    {
        if (is_null($filter))
            $filter = new BasicFilter();
        $filter->conditions = $filter->conditions === null ? [] : $filter->conditions;
        $model_class = $this->getRepositoryModelClass();
        $model = new $model_class();
        $query = $model::query();
        $query = $this->filter($filter, $query);
        if (isset($withs)) $query = $query->with($withs);
        $classUses = class_uses($model);
        if ($onlyActive && is_array($classUses)) {
            if (in_array('Illuminate\Database\Eloquent\SoftDeletes', $classUses))
                $query = $query->where('deleted_at', null);
        }

        return $query;
    }

    /**
     * Find a single object base on its id
     * @param mixed $id
     * @param string $idColumnName
     * @return Builder
     */
    function getSingleObject(mixed $id, string $idColumnName = 'id', array $withs = [], bool $trashed = false): Builder | null
    {
        if ($id == null)
            return null;
        $model_class = $this->getRepositoryModelClass();
        $query = (new $model_class())->query();
        $query = $query->where($idColumnName, $id);
        
        if ($trashed) $query = $query->withTrashed();
        if (isset($withs)) $query = $query->with($withs);
        return $query;
    }

    /**
     * Try to create the object using the given info
     * @param array<string, mixed> $form
     * @param MetaInfo|null $meta
     * @param string $idColumnName
     * @return Model
     * @throws CannotSaveToDBException
     */
    public function create(array $form, MetaInfo $meta = null, string $idColumnName = 'id'): Model
    {
        if (in_array($idColumnName, array_keys($form)))
            unset($form[$idColumnName]);

        $model_class = $this->getRepositoryModelClass();

        $entity = new $model_class();
        $entity->fill($form);
        $entity->setMetaInfo($meta, true);
        $chk = $entity->save();
        if ($chk) {
            return $entity;
        } else {
            throw new CannotSaveToDBException();
        }
    }

    /**
     * Try to save the object using the given info
     * @param array<string, mixed> $form
     * @param MetaInfo|null $meta
     * @param string $idColumnName
     * @return mixed
     * @throws CannotSaveToDBException
     * @throws IdIsNotProvidedException
     * @throws DBRecordIsNotFoundException
     */
    function update(array $form, MetaInfo $meta = null, string $idColumnName = 'id'): Model
    {
        if (!in_array('id', array_keys($form))) throw new IdIsNotProvidedException();

        $entity = $this->getSingleObject($form[$idColumnName], $idColumnName)->first();
        if (isset($entity)) {
            $entity->fill($form);
            $entity->setMetaInfo($meta, false);
            if ($entity->save() !== false) {
                return $entity;
            } else {
                throw new CannotSaveToDBException();
            }
        }
        throw new DBRecordIsNotFoundException();
    }

    /**
     * Try to delete a model based on its id
     * @param String $id
     * @param bool $soft
     * @param MetaInfo|null $meta
     * @return bool
     * @throws RecordIsNotFoundException
     */
    function delete(mixed $id, bool $soft = false, MetaInfo $meta = null, string $idColumnName = 'id'): bool
    {
        $obj = $this->getSingleObject($id, $idColumnName, [], !$soft)->first();
        if (!$soft && $obj === null) {
            throw new DBRecordIsNotFoundException();
        }
        $classUses = class_uses($obj);
        $classUses = is_array($classUses) ? $classUses : [];
        if ($soft && in_array('Illuminate\Database\Eloquent\SoftDeletes', $classUses)) {
            $obj->setMetaInfo($meta, false);
            return $obj->delete();
        } else {
            return $obj->forceDelete();
        }
    }

    /**
     * Try to restore a model record
     * @param mixed $id
     * @param bool $soft
     * @param MetaInfo|null $meta
     * @param string $idColumnName
     * @return bool
     */
    function restore(mixed $id, bool $soft = false, MetaInfo $meta = null, string $idColumnName = 'id'): bool
    {
        $model_class = $this->getRepositoryModelClass();

        $model = new $model_class();
        $classUses = class_uses($model);
        $classUses = is_array($classUses) ? $classUses : [];
        if (!in_array('Illuminate\Database\Eloquent\SoftDeletes', $classUses)) {
            return false;
        }

        return $model::withTrashed()->where($idColumnName, $id)->restore();
    }

    /**
     * Try to count matched items based on the given filter
     * @param BasicFilter|null $filter
     * @param bool $onlyActive
     * @return int
     */
    function count(BasicFilter $filter = null, bool $onlyActive = true): int
    {
        $ret = $this->search($filter, $onlyActive)->count();
        return ($ret == null) ? 0 : $ret;
    }

    /**
     * Query by applying a filter condition on a field name
     * @param array<mixed>|null $condition
     * @param Builder|null $query
     * @param array<mixed>|null $positionalBindings
     * @return Builder
     */
    function queryOnAField(array $condition = null, Builder $query = null, array $positionalBindings = null): Builder
    {
        $model_class = $this->getRepositoryModelClass();
        $query = $query ?? (new $model_class())->query();
        # 1. ensure inputs
        $condition = is_null($condition) ? [] : $condition;
        if (count($condition) == 2) {
            $new_condition = [$condition[0], "=", $condition[1]];
            return $this->queryOnAField($new_condition, $query, $positionalBindings);
        } elseif (count($condition) != 3) {
            return $query;
        }

        # 2. applying condition
        if (is_null($positionalBindings) || count($positionalBindings) == 0)
            return $query->whereRaw($condition[0] . " " . $condition[1] . " ?", $condition[2]);
        else
            return $query->whereRaw($condition[0] . " " . $condition[1] . " " . $condition[2], bindings: $positionalBindings);
    }

    /**
     * Add extra relationship field to query
     * @param array<string> $withs
     * @param Builder|null $query
     * @return Builder
     */
    function with(array $withs, Builder $query = null): Builder
    {
        $model_class = $this->getRepositoryModelClass();
        $query = $query ?? (new $model_class())->query();
        $query = $query->with($withs);
        return $query;
    }

    /**
     * Apply sort info on query
     * @param Builder $query
     * @param SortInfo|null $sort
     * @return Builder
     */
    public function sort(Builder $query, SortInfo &$sort = null): Builder
    {
        if (!isset($sort)) return $query;
        return $query->orderBy($sort->column, $sort->type);
    }

    /**
     * Apply search on created_at or updated_at
     * @param Builder $query
     * @param string $fieldName
     * @param array $rawConditions
     * @return Builder
     * @throws InvalidDatetimeInputException
     */
    public function queryOnDateRangeField(Builder $query, string $fieldName, array $rawConditions = []): Builder
    {
        try {
            if (isset($rawConditions['from'])) {
                $from = DateHelper::parse($rawConditions['from']);
                $query = $this->queryOnAField([$fieldName, '>=', $from], $query);
            }
            if (isset($rawConditions['to'])) {
                $to = DateHelper::parse($rawConditions['to'], 1);
                $query = $this->queryOnAField([$fieldName, '<', $to], $query);
            }
            return $query;
        } catch (\Exception $ex) {
            throw new InvalidDatetimeInputException();
        }
    }
}
