<?php

namespace App\Services\InvoiceTask;

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
use App\Models\InvoiceTask;
use App\Repositories\InvoiceTask\IInvoiceTaskRepository;
use App\Services\InvoiceDetail\IInvoiceDetailService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class InvoiceTaskService extends \App\Services\BaseService implements IInvoiceTaskService
{
    private ?IInvoiceTaskRepository $invoiceTaskRepos = null;
    private ?IInvoiceDetailService $invoiceDetailService = null;

    public function __construct(IInvoiceTaskRepository $repos, IInvoiceDetailService $invoiceDetailService)
    {
        $this->invoiceTaskRepos = $repos;
        $this->invoiceDetailService = $invoiceDetailService;
    }

    /**
     * Get single object of resources
     *
     * @param int $id
     * @param array<string> $withs
     * @return InvoiceTask
     * @throws RecordIsNotFoundException
     */
    public function getSingleObject(int $id, array $withs = []): InvoiceTask
    {
        try {
            $query = $this->invoiceTaskRepos->queryOnAField(['id', $id]);
            $query = $this->invoiceTaskRepos->with($withs, $query);
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
     * @return Collection<int,InvoiceTask>
     * @throws ActionFailException
     * @throws Throwable
     */
    public function search(array $rawConditions, PaginationInfo &$paging = null, array $withs = []): Collection
    {
        try {
            $query = $this->invoiceTaskRepos->search();
            // if (isset($rawConditions['month_of_year'])) {
            //     $param = StringHelper::escapeLikeQueryParameter($rawConditions['month_of_year']);
            //     $query = $this->invoiceTaskRepos->queryOnAField([DB::raw("upper(month_of_year)"), 'LIKE BINARY', DB::raw("upper(concat('%', ? , '%'))")], positionalBindings: ['month_of_year' => $param]);
            // }

            if (isset($rawConditions['id'])) {
                $param = $rawConditions['id'];
                $query = $this->invoiceTaskRepos->queryOnAField(['id', '=', $param], $query);
            }

            if (isset($rawConditions['company_id'])) {
                $param = $rawConditions['company_id'];
                $query = $this->invoiceTaskRepos->queryOnAField(['company_id', '=', $param], $query);
            }

            if (isset($rawConditions['month_of_year'])) {
                $param = $rawConditions['month_of_year'];
                $query = $this->invoiceTaskRepos->queryOnAField(['month_of_year', '=', $param], $query);
            }

            if (isset($rawConditions['year'])) {
                $param = $rawConditions['year'];
                $query = $this->invoiceTaskRepos->queryOnAField(['month_of_year', 'LIKE', "%$param"], $query);
            }

            if (isset($rawConditions['updated_date'])) {
                $query = $this->invoiceTaskRepos->queryOnDateRangeField($query, 'updated_at', $rawConditions['updated_date']);
            }
            if (isset($rawConditions['created_date'])) {
                $query = $this->invoiceTaskRepos->queryOnDateRangeField($query, 'created_at', $rawConditions['created_date']);
            }

            $query = $this->invoiceTaskRepos->with($withs, $query);


            if (!is_null($paging)) {
                $this->applyPagination($query, $paging);
            }

            if (isset($rawConditions['sort'])) {
                $sort = SortInfo::parse($rawConditions['sort']);
                return $this->invoiceTaskRepos->sort($query, $sort)->get();
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
     * @return InvoiceTask
     * @throws CannotSaveToDBException
     */
    public function create(array $param, MetaInfo $commandMetaInfo = null): InvoiceTask
    {
        DB::beginTransaction();
        try {
            #1 Create
            $record = $this->invoiceTaskRepos->create($param, $commandMetaInfo);
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
     * @return InvoiceTask
     * @throws CannotUpdateDBException
     */
    public function update(int $id, array $param, MetaInfo $commandMetaInfo = null): InvoiceTask
    {
        DB::beginTransaction();
        try {
            #1: Can edit? -> Yes: move to #2 No: return Exception with error
            $record = $this->invoiceTaskRepos->getSingleObject($id)->first();
            if (empty($record)) {
                throw new RecordIsNotFoundException();
            }
            #2: Update
            $param = array_merge($param, [
                'id' => $record->id
            ]);
            $record = $this->invoiceTaskRepos->update($param, $commandMetaInfo);
            #3: Update opening-balance-value
            // if (!empty($param['total_money_sold']) && !empty($param['total_money_purchase'])) {
            // }
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
            $record = $this->invoiceTaskRepos->getSingleObject($id, 'id', [], !$softDelete)->first();
            if (empty($record)) {
                throw new RecordIsNotFoundException();
            }
            $result =  $this->invoiceTaskRepos->delete(id: $id, soft: $softDelete, meta: $commandMetaInfo);
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
     * Handle update formula
     * @param array $params
     * key: formula -> formula
     *      formula_group -> commodity/material
     */
    public function updateHandleFormula(array $params): mixed
    {
        DB::beginTransaction();
        try {
            $key = $params['key'];
            $entities = $params['entities'];
            switch ($key) {
                case 'formula':
                    foreach ($entities as $row) {
                        $record = $this->invoiceDetailService->getSingleObject($row['id']);
                        if (empty($record)) throw new RecordsNotFoundException();
                        $record->formula_path_id = $row['value'];
                        if (!$record->save()) throw new CannotUpdateDBException();
                    }
                    $result = true;
                    break;
                case 'formula_group':
                    foreach ($entities as $row) {
                        $record = $this->invoiceDetailService->getSingleObject($row['id']);
                        if (empty($record)) throw new RecordsNotFoundException();
                        if ($record->formula_path_id) {
                            # Get group commodity/material
                            $group = explode(',', $record->formula_path_id)[3];
                            # Get id group
                            $group_string = explode('|', $row['value']);
                            $group_id = $group_string[0];
                            $group_name = $group_string[1];
                            $group_id = is_numeric($group_id) ? $group_id : null;
                            switch ($group) {
                                case 'commodity':
                                    $record->formula_commodity_id = $group_id;
                                    $record->formula_material_id = null;
                                    $record->formula_group_name = $group_id ? $group_name : '';
                                    if (!$record->save()) throw new CannotUpdateDBException();
                                    break;
                                case 'material':
                                    $record->formula_commodity_id = null;
                                    $record->formula_material_id = $group_id;
                                    $record->formula_group_name = $group_id ? $group_name : '';
                                    if (!$record->save()) throw new CannotUpdateDBException();
                                    break;
                                default:
                                    # code...
                                    break;
                            }
                        }
                    }
                    $result = true;
                    break;
                case 'warehouse':
                    foreach ($entities as $row) {
                        $record = $this->invoiceDetailService->getSingleObject($row['id']);
                        if (empty($record)) throw new RecordsNotFoundException();
                        $record->warehouse = $row['value'];
                        if (!$record->save()) throw new CannotUpdateDBException();
                    }
                    $result = true;
                    break;
                default:
                    throw new ActionFailException('key not found');
                    break;
            }
            DB::commit();
            return $result;
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new CannotUpdateDBException(
                message: 'update formula failure',
                previous: $ex
            );
        }
    }

    /**
     * Get money of months
     * @param $params
     */
    public function getMoneyOfMonths(array $params): mixed
    {
        DB::beginTransaction();
        try {
            $result = $this->invoiceTaskRepos->getMoneyOfMonths($params['company_id'], $params['year']);
            DB::commit();
            return $result;
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new ActionFailException(
                message: 'action failed',
                previous: $ex
            );
        }
    }
}
