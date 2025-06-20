<?php

namespace App\Services\InvoiceDetail;

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
use App\Models\InvoiceDetail;
use App\Repositories\InvoiceDetail\IInvoiceDetailRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class InvoiceDetailService extends \App\Services\BaseService implements IInvoiceDetailService
{
    private ?IInvoiceDetailRepository $invoiceDetailRepos = null;

    public function __construct(IInvoiceDetailRepository $repos)
    {
        $this->invoiceDetailRepos = $repos;
    }

    /**
     * Get single object of resources
     *
     * @param int $id
     * @param array<string> $withs
     * @return InvoiceDetail
     * @throws RecordIsNotFoundException
     */
    public function getSingleObject(int $id, array $withs = []): InvoiceDetail
    {
        try {
            $query = $this->invoiceDetailRepos->queryOnAField(['id', $id]);
            $query = $this->invoiceDetailRepos->with($withs, $query);
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
     * @return Collection<int,InvoiceDetail>
     * @throws ActionFailException
     * @throws Throwable
     */
    public function search(array $rawConditions, PaginationInfo &$paging = null, array $withs = []): Collection
    {
        try {
            $query = $this->invoiceDetailRepos->search();
            if (isset($rawConditions['product'])) {
                $param = StringHelper::escapeLikeQueryParameter($rawConditions['product']);
                $query = $this->invoiceDetailRepos->queryOnAField([DB::raw("upper(product)"), 'LIKE BINARY', DB::raw("upper(concat('%', ? , '%'))")], positionalBindings: ['product' => $param]);
            }

            if (isset($rawConditions['unit'])) {
                $param = StringHelper::escapeLikeQueryParameter($rawConditions['unit']);
                $query = $this->invoiceDetailRepos->queryOnAField([DB::raw("upper(unit)"), 'LIKE BINARY', DB::raw("upper(concat('%', ? , '%'))")], positionalBindings: ['unit' => $param]);
            }

            if (isset($rawConditions['type'])) {
                $type = $rawConditions['type'];
                $query->whereHas('invoice', function ($q) use ($type) {
                    $q->where('type', $type);
                });
            }

            if (isset($rawConditions['product_code'])) {
                $query->whereHas('item_code', fn($q) => $q->whereRaw('LOWER(product_code) LIKE ?', ['%' . mb_strtolower($rawConditions['product_code']) . '%']));
            }

            if (isset($rawConditions['product_name_from_item_code'])) {
                $query->whereHas('item_code', fn($q) => $q->whereRaw('LOWER(product) LIKE ?', ['%' . mb_strtolower($rawConditions['product_name_from_item_code']) . '%']));
            }

            if (isset($rawConditions['unit_from_item_code'])) {
                $query->whereHas('item_code', fn($q) => $q->whereRaw('LOWER(unit) LIKE ?', ['%' . mb_strtolower($rawConditions['unit_from_item_code']) . '%']));
            }

            if (isset($rawConditions['company_id'])) {
                $company_id = $rawConditions['company_id'];
                $query->whereHas('invoice', function ($q) use ($company_id) {
                    $q->where('company_id', $company_id);
                });
            }

            if (isset($rawConditions['start_date']) && isset($rawConditions['end_date'])) {
                $start_date = $rawConditions['start_date'];
                $end_date = $rawConditions['end_date'];
                $query->whereHas('invoice', function ($q) use ($start_date, $end_date) {
                    $q->whereDate('date', '>=', $start_date)->whereDate('date', '<=', $end_date);
                });
            }

            if (isset($rawConditions['price_from'])) {
                $param = $rawConditions['price_from'];
                $query = $this->invoiceDetailRepos->queryOnAField(['price', '>=', $param], $query);
            }

            if (isset($rawConditions['price_to'])) {
                $param = $rawConditions['price_to'];
                $query = $this->invoiceDetailRepos->queryOnAField(['price', '<=', $param], $query);
            }

            if (isset($rawConditions['updated_date'])) {
                $query = $this->invoiceDetailRepos->queryOnDateRangeField($query, 'updated_at', $rawConditions['updated_date']);
            }
            if (isset($rawConditions['created_date'])) {
                $query = $this->invoiceDetailRepos->queryOnDateRangeField($query, 'created_at', $rawConditions['created_date']);
            }

            $query = $this->invoiceDetailRepos->with($withs, $query);


            if (!is_null($paging)) {
                $this->applyPagination($query, $paging);
            }

            if (isset($rawConditions['sort'])) {
                $sort = SortInfo::parse($rawConditions['sort']);
                return $this->invoiceDetailRepos->sort($query, $sort)->get();
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
     * @return InvoiceDetail
     * @throws CannotSaveToDBException
     */
    public function create(array $param, MetaInfo $commandMetaInfo = null): InvoiceDetail
    {
        DB::beginTransaction();
        try {
            #1 Create
            $record = $this->invoiceDetailRepos->create($param, $commandMetaInfo);
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
     * @return InvoiceDetail
     * @throws CannotUpdateDBException
     */
    public function update(int $id, array $param, MetaInfo $commandMetaInfo = null): InvoiceDetail
    {
        DB::beginTransaction();
        try {
            #1: Can edit? -> Yes: move to #2 No: return Exception with error
            $record = $this->invoiceDetailRepos->getSingleObject($id)->first();
            if (empty($record)) {
                throw new RecordIsNotFoundException();
            }
            #2: update
            $param = array_merge($param, [
                'id' => $record->id
            ]);
            $record = $this->invoiceDetailRepos->update($param, $commandMetaInfo);
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
            $record = $this->invoiceDetailRepos->getSingleObject($id)->first();
            if (empty($record)) {
                throw new RecordIsNotFoundException();
            }
            $result =  $this->invoiceDetailRepos->delete(id: $id, soft: $softDelete, meta: $commandMetaInfo);
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
     * Update formula for inovice detail
     * @return bool
     */
    public function updateProgressByFormula(array $param, MetaInfo $commandMetaInfo = null): bool
    {
        DB::beginTransaction();
        try {
            $result =  $this->invoiceDetailRepos->updateProgressByFormula($param, $commandMetaInfo);
            DB::commit();
            return $result;
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new ActionFailException(
                message: 'update progress',
                previous: $ex
            );
        }
    }
}
