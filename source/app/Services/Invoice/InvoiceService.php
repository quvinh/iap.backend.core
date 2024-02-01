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
use App\Helpers\Utils\RoundMoneyHelper;
use App\Helpers\Utils\StorageHelper;
use App\Helpers\Utils\StringHelper;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\InvoiceTask;
use App\Models\ItemCode;
use App\Repositories\Invoice\IInvoiceRepository;
use App\Services\Company\ICompanyService;
use App\Services\InvoiceDetail\IInvoiceDetailService;
use App\Services\InvoiceTask\IInvoiceTaskService;
use App\Services\ItemCode\IItemCodeService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class InvoiceService extends \App\Services\BaseService implements IInvoiceService
{
    private ?IInvoiceRepository $invoiceRepos = null;
    private ?ICompanyService $companyService = null;
    private ?IInvoiceTaskService $invoiceTaskService = null;
    private ?IItemCodeService $itemCodeService = null;
    private ?IInvoiceDetailService $invoiceDetailService = null;

    public function __construct(
        IInvoiceRepository $repos,
        ICompanyService $companyService,
        IInvoiceTaskService $invoiceTaskService,
        IItemCodeService $itemCodeService,
        IInvoiceDetailService $invoiceDetailService
    ) {
        $this->invoiceRepos = $repos;

        $this->companyService = $companyService;
        $this->invoiceTaskService = $invoiceTaskService;
        $this->itemCodeService = $itemCodeService;
        $this->invoiceDetailService = $invoiceDetailService;
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
            if (!is_null($record))
                return $record;
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
     * @return Collection<int,Invoice>
     * @throws ActionFailException
     * @throws Throwable
     */
    public function search(array $rawConditions, PaginationInfo &$paging = null, array $withs = []): Collection
    {
        try {
            $query = $this->invoiceRepos->search();

            # Sort
            // $query = $query->orderByDesc('date')->orderByDesc('invoice_number');

            if (isset($rawConditions['name'])) {
                $param = StringHelper::escapeLikeQueryParameter($rawConditions['name']);
                $query = $this->invoiceRepos->queryOnAField([DB::raw("upper(name)"), 'LIKE BINARY', DB::raw("upper(concat('%', ? , '%'))")], positionalBindings: ['name' => $param]);
            }

            if (isset($rawConditions['id'])) {
                $param = $rawConditions['id'];
                $query = $this->invoiceRepos->queryOnAField(['id', '=', $param], $query);
            }

            if (isset($rawConditions['company_id'])) {
                $param = $rawConditions['company_id'];
                $query = $this->invoiceRepos->queryOnAField(['company_id', '=', $param], $query);
            }

            if (isset($rawConditions['invoice_task_id'])) {
                $param = $rawConditions['invoice_task_id'];
                $query = $this->invoiceRepos->queryOnAField(['invoice_task_id', '=', $param], $query);
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

            if (isset($rawConditions['verification_code_status'])) {
                $param = $rawConditions['verification_code_status'];
                $query = $this->invoiceRepos->queryOnAField(['verification_code_status', '=', $param], $query);
            }

            if (isset($rawConditions['status'])) {
                $param = $rawConditions['status'];
                $query = $this->invoiceRepos->queryOnAField(['status', '=', $param], $query);
            }

            if (isset($rawConditions['locked'])) {
                $param = $rawConditions['locked'];
                $query = $this->invoiceRepos->queryOnAField(['locked', '=', $param], $query);
            }

            if (isset($rawConditions['date'])) {
                $query = $this->invoiceRepos->queryOnDateRangeField($query, 'date', $rawConditions['date']);
            }

            if (isset($rawConditions['year'])) {
                $param = $rawConditions['year'];
                $query = $this->invoiceRepos->queryOnAField([DB::raw('year(date)'), '=', $param], $query);
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
                if (isset($rawConditions['sort_p2'])) {
                    $sort2 = SortInfo::parse($rawConditions['sort_p2']);
                    return $this->invoiceRepos->sort($query, $sort)->orderBy($sort2->column ?? 'id', $sort2->type ?? 'desc')->get();
                }
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
            $rounding = $param['rounding'] ?? 1;
            #1: Can edit? -> Yes: move to #2 No: return Exception with error
            $record = $this->invoiceRepos->getSingleObject($id)->first();
            if (empty($record)) {
                throw new RecordIsNotFoundException();
            }
            #2: Update
            $param = array_merge($param, [
                'id' => $record->id
            ]);
            $after = (object)[
                'sum_money_no_vat' => $record->sum_money_no_vat ?? 0,
                'sum_money_vat' => $record->sum_money_vat ?? 0,
                'sum_money' => $record->sum_money ?? 0,
            ];
            $record = $this->invoiceRepos->update($param, $commandMetaInfo);

            #3: Get invoice-details
            if (!empty($param['invoice_details'])) {
                $_sumMoneyNoVat = 0;
                $_sumMoneyVat = 0;
                $_sumMoneyDiscount = 0;
                $_sumMoney = 0;
                # Updated invoice-detail
                $updatedRows = array_filter($param['invoice_details'], function ($row) {
                    return !empty($row['id']); // Check ID invoice-detail
                });
                $this->invoiceRepos->deleteInvoiceDetails($record->id, array_map(function ($value) {
                    return $value['id'];
                }, $updatedRows));
                foreach ($updatedRows as $row) {
                    $item = $this->invoiceDetailService->getSingleObject($row['id']);
                    if (!empty($item)) {
                        $_vat = empty($row['vat']) ? 0 : ($row['vat'] >= 0 ? $row['vat'] : 0);
                        $total_money = $row['total_money'] ?? $item->total_money;
                        $item->product = $row['product'] ?? $item->product;
                        $item->unit = $row['unit'] ?? $item->unit;
                        $item->quantity = $row['quantity'] ?? $item->quantity;
                        $item->price = $row['price'] ?? $item->price;
                        $item->vat = $row['vat'] ?? $item->vat;
                        $item->total_money = RoundMoneyHelper::roundMoney($total_money, $rounding);
                        $item->warehouse = $row['warehouse'] ?? $item->warehouse;
                        $item->vat_money = round((floatval($_vat) * floatval($item->total_money)) / 100, 2);
                        if ($item->save()) {
                            $_sumMoneyNoVat += floatval($item->total_money);
                            $_sumMoneyVat += floatval($item->vat_money);
                            // $_sumMoneyDiscount
                        }
                    }
                }

                # Created invoice-detail
                $createdRows = array_filter($param['invoice_details'], function ($row) {
                    return empty($row['id']); // Check is not ID invoice-detail -> Create
                });
                foreach ($createdRows as $row) {
                    if ($row['invoice_id'] == $record->id) {
                        $item = $this->invoiceDetailService->create([
                            'invoice_id' => $row['invoice_id'],
                            'product' => $row['product'],
                            'unit' => $row['unit'],
                            'quantity' => $row['quantity'],
                            'price' => $row['price'],
                            'vat' => $row['vat'] ?? 0,
                            'vat_money' => round($row['vat_money'], 2),
                            'total_money' => RoundMoneyHelper::roundMoney($row['total_money'], $rounding),
                            'warehouse' => $row['warehouse'] ?? 0,
                        ], $commandMetaInfo);
                        if (!empty($item)) {
                            $_sumMoneyNoVat += floatval($row['total_money']);
                            $_sumMoneyVat += floatval($row['vat_money']);
                        }
                    }
                }
                $_sumMoney = RoundMoneyHelper::roundMoney($_sumMoneyNoVat + $_sumMoneyVat, $rounding);
                # Get new sum money
                $newMoneyNoVat = $param['sum_money_no_vat'] ?? null;
                $newMoneyVat = $param['sum_money_vat'] ?? null;
                $newMoney = $param['sum_money'] ?? null;
                # Update main invoice
                if (empty($newMoneyNoVat) || empty($newMoneyVat) || empty($newMoney)) {
                    $record->sum_money_no_vat = $_sumMoneyNoVat;
                    $record->sum_money_vat = $_sumMoneyVat;
                    // $record->sum_money_discount = $_sumMoneyDiscount;
                    $record->sum_money = $_sumMoney;
                    $record->save();
                } else {
                    $newMoneyNoVat = floatval($newMoneyNoVat) == floatval($after->sum_money_no_vat) ? $_sumMoneyNoVat : $newMoneyNoVat;
                    $newMoneyVat = floatval($newMoneyVat) == floatval($after->sum_money_vat) ? $_sumMoneyVat : $newMoneyVat;
                    $newMoney = floatval($newMoney) == floatval($after->sum_money) ? $_sumMoney : $newMoney;
                    $record->sum_money_no_vat = $newMoneyNoVat;
                    $record->sum_money_vat = $newMoneyVat;
                    $record->sum_money = $newMoney;
                    $record->save();
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
            $record = $this->invoiceRepos->getSingleObject($id)->first();
            if (empty($record)) {
                throw new RecordIsNotFoundException();
            }
            $result = $this->invoiceRepos->delete(id: $id, soft: $softDelete, meta: $commandMetaInfo);
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
            $rounding = $param['rounding'] ?? 1;
            $company_id = $param['company_id'];
            $partner_tax_code = $param['partner_tax_code'];
            $type = $param['type'];
            $invoice_number = $param['invoice_number'];
            $invoice_symbol = $param['invoice_symbol'];
            $unit = Str::lower($param['unit']);

            # 0.Compare price * quantity with total_money
            // if ($param['price'] * $param['quantity'] != $param['total_money']) throw new ActionFailException();

            # 1.Check task
            $task_month = Carbon::parse($param['date'])->format('m/Y');
            $year = Carbon::parse($param['date'])->format('Y');
            $task = $this->invoiceTaskService->search([
                'company_id' => $company_id,
                'month_of_year' => $task_month,
            ]);
            if (empty($task->first())) {
                $task = new InvoiceTask();
                $task->company_id = $company_id;
                $task->month_of_year = $task_month;
                $task->save();
            } else
                $task = $task->first();
            # 2.Check invoice
            # Need store initialize invoice -> json
            $invoice = $this->search([
                'company_id' => $company_id,
                'partner_tax_code' => $partner_tax_code,
                'type' => $type,
                'invoice_number' => $invoice_number,
                'invoice_symbol' => $invoice_symbol,
                'year' => $year,
            ]);
            if (empty($invoice->first())) {
                $invoice = new Invoice();
                $invoice->company_id = $company_id;
                $invoice->invoice_task_id = $task->id;
                $invoice->partner_tax_code = $partner_tax_code;
                $invoice->partner_name = $param['partner_name'] ?? null;
                $invoice->partner_address = $param['partner_address'] ?? null;
                $invoice->type = $type;
                $invoice->invoice_number = $invoice_number;
                $invoice->invoice_symbol = $invoice_symbol;
                $invoice->date = $param['date'];
                $invoice->invoice_number_form = $param['invoice_number_form'] ?? 1; # Warning
                $invoice->verification_code_status = $param['verification_code_status'] ?? 1; # Co ma co quan thue
                $invoice->created_by = auth()->user()->id . '|' . auth()->user()->name;
                $invoice->save();
            } else {
                $invoice = $invoice->first();
                if ($invoice->invoice_task_id != $task->id) {
                    throw new ActionFailException(message: "Invoice task ID:{$invoice->invoice_task_id} not match with task ID: {$task->id}");
                }
            }
            # 3.Check item code
            #TODO: Nhap lieu hoa don chi kem ma hh, khong fill ten quy doi
            if (isset($param['product_code'])) {
                $itemCode = $this->itemCodeService->search([
                    'company_id' => $company_id,
                    'year' => $year,
                    'product_code' => $param['product_code']
                ]);
                if (empty($itemCode->first())) {
                    $itemCode = new ItemCode();
                    $itemCode->company_id = $company_id;
                    $itemCode->product_code = $param['product_code'];
                    $itemCode->product = $param['product'];
                    $itemCode->price = $param['price'];
                    $itemCode->quantity = $param['quantity'];
                    // $itemCode->opening_balance_value = $param['total_money'];
                    $itemCode->unit = $unit;
                    $itemCode->year = $year;
                    $itemCode->setItemCode($param['quantity'] ?? 1, $param['price']);
                    $itemCode->save();
                } else
                    $itemCode = $itemCode->first();
            } else {
                // nothing
            }
            # 4.Store invoice detail
            $invoiceDetail = new InvoiceDetail();
            $invoiceDetail->invoice_id = $invoice->id;
            $invoiceDetail->item_code_id = $itemCode->id ?? null;
            $invoiceDetail->product = $param['product'];
            $invoiceDetail->product_exchange = $param['product_exchange'] ?? null;
            $invoiceDetail->unit = $unit;
            // $invoiceDetail->quantity = $param['quantity'];
            // $invoiceDetail->price = $param['price'];
            $invoiceDetail->setInvoiceDetail($param['quantity'], $param['price'], $param['vat']);
            $invoiceDetail->save();
            # 5.Update sum value of invoice
            $invoice->plusMoneyInvoice($invoiceDetail->total_money, $invoiceDetail->vat);
            $invoice->save();

            DB::commit();
            # 6.Return
            return $invoice;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new CannotSaveToDBException(
                message: 'create: ' . json_encode(['param' => $param]),
                previous: $e
            );
        }
    }

    /**
     * import invoice from excel's data
     *
     * @param array $param
     * @param MetaInfo|null $commandMetaInfo
     * @throws ActionFailException
     */
    public function import(array $param, MetaInfo $commandMetaInfo = null): mixed
    {
        DB::beginTransaction();
        try {
            $rounding = $param['rounding'] ?? 1;
            foreach ($param['invoice_details'] as $index => $row) {
                $record = $this->storeEachRowInvoice([
                    'company_id' => $param['company_id'],
                    'type' => $param['type'],
                    'date' => $row['date'],
                    'partner_name' => $row['partner_name'],
                    'partner_address' => $row['partner_address'] ?? null,
                    'partner_tax_code' => $row['partner_tax_code'],
                    'invoice_number' => $row['invoice_number'],
                    'invoice_number_form' => $row['invoice_number_form'] ?? 1,
                    'invoice_symbol' => $row['invoice_symbol'],
                    'product' => $row['product'],
                    'product_code' => $row['product_code'] ?? null,
                    'product_exchange' => $row['product_exchange'] ?? null,
                    'unit' => $row['unit'],
                    'vat' => $row['vat'],
                    'quantity' => $row['quantity'],
                    'price' => $row['price'],
                    'verification_code_status' => $param['verification_code_status'] ?? 1, # Co ma co quan thue
                    'rounding' => $rounding,
                ], $commandMetaInfo);
                if (empty($record)) {
                    $index += 1;
                    throw new ActionFailException(message: "Failure at row $index");
                }
            }

            DB::commit();
            return $record;
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error(message: $ex->getMessage());
            if ($ex instanceof ActionFailException) {
                return [
                    'status' => false,
                    'message' => $ex->getMessage()
                ];
            }
            throw new Exception($ex);
        }
    }

    /**
     * restore invoice
     */
    public function restoreRowsInvoice(mixed $id, MetaInfo $commandMetaInfo = null): mixed
    {
        DB::beginTransaction();
        try {
            $record = $this->invoiceRepos->getSingleObject($id)->first();
            if (empty($record)) {
                throw new RecordIsNotFoundException();
            }
            $invoiceDetails = (object) json_decode($record->json);
            if (empty($invoiceDetails->invoice_details)) {
                throw new ActionFailException();
            } else {
                $_sumMoneyNoVat = 0;
                $_sumMoneyVat = 0;
                $_sumMoneyDiscount = 0;
                $_sumMoney = 0;
                $this->invoiceRepos->deleteInvoiceDetails($record->id, []);
                foreach ($invoiceDetails->invoice_details as $row) {
                    if ($row->invoice_id == $record->id) {
                        $item = $this->invoiceDetailService->create([
                            'invoice_id' => $row->invoice_id,
                            'product' => $row->product,
                            'unit' => $row->unit,
                            'quantity' => $row->quantity,
                            'price' => $row->price,
                            'vat' => $row->vat ?? 0,
                            'vat_money' => $row->vat_money,
                            'total_money' => $row->total_money,
                            'warehouse' => $row->warehouse ?? 0,
                        ], $commandMetaInfo);
                        if (!empty($item)) {
                            $_sumMoneyNoVat += floatval($row->total_money);
                            $_sumMoneyVat += floatval($row->vat_money);
                        }
                    }
                }
                # Update main invoice
                $_sumMoney = $_sumMoneyNoVat + $_sumMoneyVat;
                $record->sum_money_no_vat = $_sumMoneyNoVat;
                $record->sum_money_vat = $_sumMoneyVat;
                // $record->sum_money_discount = $_sumMoneyDiscount;
                $record->sum_money = $_sumMoney;
                $record->save();
            }
            DB::commit();
            return $record;
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new CannotDeleteDBException(
                message: 'restore rows: ' . json_encode(['id' => $id]),
                previous: $ex
            );
        }
    }

    /**
     * Find partners by company_id
     */
    public function findPartnersByCompanyId(mixed $company_id, mixed $year): mixed
    {
        return $this->invoiceRepos->findPartnersByCompanyId($company_id, $year);
    }

    /**
     * Info invoices
     */
    public function info(array $params): array
    {
        return $this->invoiceRepos->info($params);
    }

    /**
     * Find next invoice
     */
    public function findNextInvoice(array $params): Invoice | EloquentCollection | null
    {
        return $this->invoiceRepos->findNextInvoice($params);
    }

    /**
     * Report sold
     */
    public function reportSold(array $params): EloquentCollection | Invoice | array | null
    {
        return $this->invoiceRepos->reportSold($params);
    }
}