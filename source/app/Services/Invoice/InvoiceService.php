<?php

namespace App\Services\Invoice;

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
use App\Models\Invoice;
use App\Models\InvoiceTask;
use App\Repositories\Invoice\IInvoiceRepository;
use App\Services\Company\ICompanyService;
use App\Services\InvoiceTask\IInvoiceTaskService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class InvoiceService extends \App\Services\BaseService implements IInvoiceService
{
    private ?IInvoiceRepository $invoiceRepos = null;
    private ?ICompanyService $companyService = null;
    private ?IInvoiceTaskService $invoiceTaskService = null;

    public function __construct(IInvoiceRepository $repos, ICompanyService $companyService, IInvoiceTaskService $invoiceTaskService)
    {
        $this->invoiceRepos = $repos;

        $this->companyService = $companyService;
        $this->invoiceTaskService = $invoiceTaskService;
    }

    /**
     * Get single object of resources
     *
     * @param int $id
     * @param array<string> $withs
     * @return Invoice
     * @throws RecordIsNotFoundException
     */
    public function getSingleObject(int $id, array $withs = []): Invoice
    {
        try {
            $query = $this->invoiceRepos->queryOnAField(['id', $id]);
            $query = $this->invoiceRepos->with($withs, $query);
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
     * @return Collection<int,Invoice>
     * @throws ActionFailException
     * @throws Throwable
     */
    public function search(array $rawConditions, PaginationInfo &$paging = null, array $withs = []): Collection
    {
        try {
            $query = $this->invoiceRepos->search();
            if (isset($rawConditions['name'])) {
                $param = StringHelper::escapeLikeQueryParameter($rawConditions['name']);
                $query = $this->invoiceRepos->queryOnAField([DB::raw("upper(name)"), 'LIKE BINARY', DB::raw("upper(concat('%', ? , '%'))")], positionalBindings: ['name' => $param]);
            }

            if (isset($rawConditions['company_id'])) {
                $param = $rawConditions['company_id'];
                $query = $this->invoiceRepos->queryOnAField(['company_id', '=', $param], $query);
            }

            if (isset($rawConditions['partner_tax_code'])) {
                $param = $rawConditions['partner_tax_code'];
                $query = $this->invoiceRepos->queryOnAField(['partner_tax_code', '=', $param], $query);
            }

            if (isset($rawConditions['type'])) {
                $param = $rawConditions['type'];
                $query = $this->invoiceRepos->queryOnAField(['type', '=', $param], $query);
            }

            if (isset($rawConditions['invoice_number'])) {
                $param = $rawConditions['invoice_number'];
                $query = $this->invoiceRepos->queryOnAField(['invoice_number', '=', $param], $query);
            }

            if (isset($rawConditions['invoice_symbol'])) {
                $param = $rawConditions['invoice_symbol'];
                $query = $this->invoiceRepos->queryOnAField(['invoice_symbol', '=', $param], $query);
            }

            if (isset($rawConditions['updated_date'])) {
                $query = $this->invoiceRepos->queryOnDateRangeField($query, 'updated_at', $rawConditions['updated_date']);
            }
            if (isset($rawConditions['created_date'])) {
                $query = $this->invoiceRepos->queryOnDateRangeField($query, 'created_at', $rawConditions['created_date']);
            }

            $query = $this->invoiceRepos->with($withs, $query);


            if (!is_null($paging)) {
                $this->applyPagination($query, $paging);
            }

            if (isset($rawConditions['sort'])) {
                $sort = SortInfo::parse($rawConditions['sort']);
                return $this->invoiceRepos->sort($query, $sort)->get();
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
     * @return Invoice
     * @throws CannotSaveToDBException
     */
    public function create(array $param, MetaInfo $commandMetaInfo = null): Invoice
    {
        DB::beginTransaction();
        try {
            #1 Create
            $record = $this->invoiceRepos->create($param, $commandMetaInfo);
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
     * @return Invoice
     * @throws CannotUpdateDBException
     */
    public function update(int $id, array $param, MetaInfo $commandMetaInfo = null): Invoice
    {
        DB::beginTransaction();
        try {
            #1: Can edit? -> Yes: move to #2 No: return Exception with error
            $record = $this->invoiceRepos->getSingleObject($id)->first();
            if (empty($record)) {
                throw new RecordIsNotFoundException();
            }
            #2: update
            $param = array_merge($param, [
                'id' => $record->id
            ]);
            $record = $this->invoiceRepos->update($param, $commandMetaInfo);
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
            $record = $this->invoiceRepos->getSingleObject($id)->first();
            if (empty($record)) {
                throw new RecordIsNotFoundException();
            }
            $result =  $this->invoiceRepos->delete(id: $id, soft: $softDelete, meta: $commandMetaInfo);
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
     * Store invoice from each row of excel's data
     *
     * @param array $param
     * @param MetaInfo|null $commandMetaInfo
     * @return Invoice
     * @throws CannotSaveToDBException
     */
    public function storeEachRowInvoice(array $param, MetaInfo $commandMetaInfo = null): Invoice
    {
        DB::beginTransaction();
        try {
            $company_id = $param['company_id'];
            $partner_tax_code = $param['partner_tax_code'];
            $type = $param['type'];
            $invoice_number = $param['invoice_number'];
            $invoice_symbol = $param['invoice_symbol']; 

            #1 Check task
            $task_month = Carbon::parse($param['date'])->format('m/Y');
            $task = $this->invoiceTaskService->search([
                'company_id' => $company_id,
                'month_of_year' => $task_month,
            ]);
            if (empty($task->first())) {
                $task = new InvoiceTask();
                $task->company_id = $company_id;
                $task->month_of_year = $task_month;
                $task->save();
            } else $task = $task->first();
            #2 Check invoice
            $invoice = $this->search([
                'company_id' => $company_id,
                'partner_tax_code' => $partner_tax_code,
                'type' => $type,
                'invoice_number' => $invoice_number,
                'invoice_symbol' => $invoice_symbol,
            ]);
            if (empty($invoice->first())) {
                $invoice = new Invoice();
                $invoice->company_id = $company_id;
                $invoice->invoice_task_id = $task->id;
                $invoice->partner_tax_code = $partner_tax_code;
                $invoice->type = $type;
                $invoice->invoice_number = $invoice_number;
                $invoice->invoice_symbol = $invoice_symbol;
                $invoice->date = $param['date'];
                $invoice->save();
            } else {
                $invoice = $invoice->first();
                if ($invoice->invoice_task_id != $task->id) {
                    throw new ActionFailException();
                }
            }
            #3 Check item code
            if (isset($param['product_code'])) {

            } else {

            }
            #4 Store invoice detail
            DB::commit();
            #5 Return
            return $invoice;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new CannotSaveToDBException(
                message: 'create: ' . json_encode(['param' => $param]),
                previous: $e
            );
        }
    }
}
