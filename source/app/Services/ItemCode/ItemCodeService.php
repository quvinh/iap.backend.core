<?php

namespace App\Services\ItemCode;

use App\DataResources\PaginationInfo;
use App\DataResources\SortInfo;
use App\Exceptions\Business\ActionFailException;
use App\Exceptions\DB\CannotDeleteDBException;
use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\CannotUpdateDBException;
use App\Exceptions\DB\RecordIsNotFoundException;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\ItemCodeMetaSlugEnum;
use App\Helpers\Utils\StorageHelper;
use App\Helpers\Utils\StringHelper;
use App\Models\InvoiceDetail;
use App\Models\ItemCode;
use App\Repositories\ItemCode\IItemCodeRepository;
use App\Services\User\IUserService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ItemCodeService extends \App\Services\BaseService implements IItemCodeService
{
    private ?IItemCodeRepository $itemCodeRepos = null;
    private ?IUserService $userService = null;

    public function __construct(IItemCodeRepository $repos, IUserService $userService)
    {
        $this->itemCodeRepos = $repos;
        $this->userService = $userService;
    }

    /**
     * Get single object of resources
     *
     * @param int $id
     * @param array<string> $withs
     * @return ItemCode
     * @throws RecordIsNotFoundException
     */
    public function getSingleObject(int $id, array $withs = []): ItemCode
    {
        try {
            $query = $this->itemCodeRepos->queryOnAField(['id', $id]);
            $query = $this->itemCodeRepos->with($withs, $query);
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
     * @return Collection<int,ItemCode>
     * @throws ActionFailException
     * @throws Throwable
     */
    public function search(array $rawConditions, PaginationInfo &$paging = null, array $withs = []): Collection
    {
        try {
            $query = $this->itemCodeRepos->search();

            if (isset($rawConditions['product_code'])) {
                $param = StringHelper::escapeLikeQueryParameter($rawConditions['product_code']);
                $query = $this->itemCodeRepos->queryOnAField([DB::raw("upper(product_code)"), 'LIKE BINARY', DB::raw("upper(concat('%', ? , '%'))")], positionalBindings: ['product_code' => $param]);
            }

            if (isset($rawConditions['product_exchange'])) {
                $param = StringHelper::escapeLikeQueryParameter($rawConditions['product_exchange']);
                $query = $this->itemCodeRepos->queryOnAField([DB::raw("upper(product_exchange)"), 'LIKE BINARY', DB::raw("upper(concat('%', ? , '%'))")], $query, positionalBindings: ['product_exchange' => $param]);
            }

            if (isset($rawConditions['product'])) {
                $param = StringHelper::escapeLikeQueryParameter($rawConditions['product']);
                $query = $this->itemCodeRepos->queryOnAField([DB::raw("upper(product)"), 'LIKE BINARY', DB::raw("upper(concat('%', ? , '%'))")], $query, positionalBindings: ['product' => $param]);
            }

            if (isset($rawConditions['unit'])) {
                $param = StringHelper::escapeLikeQueryParameter($rawConditions['unit']);
                $query = $this->itemCodeRepos->queryOnAField([DB::raw("upper(unit)"), 'LIKE BINARY', DB::raw("upper(concat('%', ? , '%'))")], $query);
            }

            if (isset($rawConditions['company_id'])) {
                $param = $rawConditions['company_id'];
                $query = $this->itemCodeRepos->queryOnAField(['company_id', '=', $param], $query);
            }

            if (isset($rawConditions['year'])) {
                $param = $rawConditions['year'];
                $query = $this->itemCodeRepos->queryOnAField(['year', '=', $param], $query);
            }

            if (isset($rawConditions['status'])) {
                $param = $rawConditions['status'];
                $query = $this->itemCodeRepos->queryOnAField(['status', '=', $param], $query);
            }

            if (isset($rawConditions['price_from'])) {
                $param = $rawConditions['price_from'];
                $query = $this->itemCodeRepos->queryOnAField(['price', '>=', $param], $query);
            }

            if (isset($rawConditions['price_to'])) {
                $param = $rawConditions['price_to'];
                $query = $this->itemCodeRepos->queryOnAField(['price', '<=', $param], $query);
            }

            if (isset($rawConditions['updated_date'])) {
                $query = $this->itemCodeRepos->queryOnDateRangeField($query, 'updated_at', $rawConditions['updated_date']);
            }
            if (isset($rawConditions['created_date'])) {
                $query = $this->itemCodeRepos->queryOnDateRangeField($query, 'created_at', $rawConditions['created_date']);
            }

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

            $query = $this->itemCodeRepos->with($withs, $query);


            if (!is_null($paging)) {
                $this->applyPagination($query, $paging);
            }

            if (isset($rawConditions['sort'])) {
                $sort = SortInfo::parse($rawConditions['sort']);
                return $this->itemCodeRepos->sort($query, $sort)->get();
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
     * @return ItemCode
     * @throws CannotSaveToDBException
     */
    public function create(array $param, MetaInfo $commandMetaInfo = null): ItemCode
    {
        DB::beginTransaction();
        try {
            #1 Create
            $record = $this->itemCodeRepos->create($param, $commandMetaInfo);
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
     * @return ItemCode
     * @throws CannotUpdateDBException
     */
    public function update(int $id, array $param, MetaInfo $commandMetaInfo = null): ItemCode
    {
        DB::beginTransaction();
        try {
            #1: Can edit? -> Yes: move to #2 No: return Exception with error
            $record = $this->itemCodeRepos->getSingleObject($id)->first();
            if (empty($record)) {
                throw new RecordIsNotFoundException();
            }
            #2: update
            $param = array_merge($param, [
                'id' => $record->id
            ]);
            $record = $this->itemCodeRepos->update($param, $commandMetaInfo);
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
            $record = $this->itemCodeRepos->getSingleObject($id)->first();
            if (empty($record)) {
                throw new RecordIsNotFoundException();
            }
            $result =  $this->itemCodeRepos->delete(id: $id, soft: $softDelete, meta: $commandMetaInfo);
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
     * Handle import excel
     * 
     * @param array $param
     * @param MetaInfo|null $commandMetaInfo
     * @return bool
     */
    public function import(array $param, MetaInfo $commandMetaInfo = null): array
    {
        DB::beginTransaction();
        try {
            foreach ($param['import'] as $index => $item) {
                # Find record
                $entity = (new ItemCode())->query()->where([
                    ['company_id', $param['company_id']],
                    ['year', $param['year']],
                    ['product_code', $item['product_code']],
                ])->first();
                $item = array_merge($item, [
                    'year' => $param['year'],
                    'company_id' => $param['company_id'],
                    'unit' => Str::lower($item['unit']),
                ]);
                if ($entity == null) {
                    # Create by code
                    $this->itemCodeRepos->create($item, $commandMetaInfo);
                } else {
                    $item = array_merge($item, [
                        'id' => $entity->id,
                        'quantity' => $item['quantity'] ?? $entity->quantity,
                    ]);
                    # Check with product
                    if (empty($item['product'])) {
                        # Update by code
                        $this->itemCodeRepos->update($item, $commandMetaInfo);
                    } elseif ($entity->product == $item['product']) {
                        # Update by code
                        $this->itemCodeRepos->update($item, $commandMetaInfo);
                    } else throw new ActionFailException(message: "product not match at row " . ($index + 1));
                }
            }
            DB::commit();
            return ['status' => true];
        } catch (\Exception $ex) {
            DB::rollBack();
            if ($ex instanceof ActionFailException) {
                return [
                    'status' => false,
                    'message' => $ex->getMessage()
                ];
            }
            throw new Exception($ex);
        }
    }

    public function getAll(array $params): EloquentCollection
    {
        return $this->itemCodeRepos->getAll($params)->get(['id', 'product_code', 'product', 'price', 'quantity', 'opening_balance_value', 'unit']);
    }

    public function autoFill(array $params): mixed
    {
        $sortType = 'desc';
        $page = 1;
        $per_page = 50;
        $company_id = null;
        $year = $params['year'] ?? date('Y');
        $query = InvoiceDetail::query()->select(['id', 'invoice_id', 'product', 'item_code_id', 'price']);

        if (isset($params['pagination'])) {
            $pagination = $params['pagination'];
            $page = $pagination['page'];
            $per_page = $pagination['per_page'];
        }

        if (isset($params['sort'])) {
            $sort = $params['sort'];
            $sortType = $sort['type'] ?? 'desc';
        }

        if (isset($params['type'])) {
            $type = $params['type'];
            $query->whereHas('invoice', function ($q) use ($type) {
                $q->where('type', $type);
            });
        }

        if (isset($params['company_id'])) {
            $company_id = $params['company_id'];
            $query->whereHas('invoice', function ($q) use ($company_id) {
                $q->where('company_id', $company_id);
            });
        }

        if (isset($params['start_date']) && isset($params['end_date'])) {
            $start_date = $params['start_date'];
            $end_date = $params['end_date'];
            $query->whereHas('invoice', function ($q) use ($start_date, $end_date) {
                $q->whereDate('date', '>=', $start_date)->whereDate('date', '<=', $end_date);
            });
        }

        if (isset($params['price_from'])) {
            $price_from = $params['price_from'];
            $query->where('price', '>=', $price_from);
        }

        if (isset($params['price_to'])) {
            $price_to = $params['price_to'];
            $query->where('price', '<=', $price_to);
        }

        # Pagination
        $query->orderBy('id', $sortType)->paginate($per_page, ['*'], 'page', $page);

        $invoiceDetails = $query->get();

        $results = [];
        $threshold = $params['percent_similar'] ?? 55; // Ngưỡng tương đồng
        $difference_ratio = $params['difference_ratio'] ?? 10;

        foreach ($invoiceDetails as $item) {
            $product = $item->product;
            $bestMatch = null;
            $highestPercent = 0;
            $note = 'Tạo mới';
            $slug = ItemCodeMetaSlugEnum::NEED_TO_CREATE;

            if (empty($item->item_code_id)) {
                // Lặp qua item_codes bằng chunk với Eloquent
                ItemCode::query()->select('id', 'item_group_id', 'product', 'product_code', 'price', 'unit', 'quantity')
                    ->whereNotNull('product')
                    ->where('company_id', $company_id)
                    ->where('year', $year)
                    ->chunk(100, function ($rows) use ($product, $threshold, $item, $difference_ratio, &$bestMatch, &$highestPercent) {
                        foreach ($rows as $row) {
                            $normalizedProduct = StringHelper::normalizeVietnameseString($product);
                            $normalizedRowProduct = StringHelper::normalizeVietnameseString($row->product);
                            similar_text($normalizedProduct, $normalizedRowProduct, $percent);

                            // Kiểm tra giá nếu có, và chỉ so sánh khi $row->price > 0
                            $isPriceMatch = true;
                            if ($item->price && $row->price > 0) {
                                $minPrice = $item->price * (1 - $difference_ratio / 100);
                                $maxPrice = $item->price * (1 + $difference_ratio / 100);
                                $isPriceMatch = $row->price >= $minPrice && $row->price <= $maxPrice;
                            }

                            // Cập nhật bestMatch nếu thỏa mãn cả độ tương đồng và giá
                            if ($percent >= $threshold && $percent > $highestPercent && $isPriceMatch) {
                                $highestPercent = $percent;
                                $bestMatch = [
                                    'id' => $row->id,
                                    'product_code' => $row->product_code,
                                    'product' => $row->product,
                                    'price' => $row->price,
                                    'unit' => $row->unit,
                                    'quantity' => $row->quantity,
                                    'percent' => round($percent, 2),
                                ];
                            }
                        }
                    });

                // Update slug
                if (!empty($bestMatch)) {
                    $note = 'Lọc tự động';
                    $slug = ItemCodeMetaSlugEnum::AUTO_FILL;
                }
            } else {
                $note = 'Đã lưu';
                $slug = ItemCodeMetaSlugEnum::SAVED;
            }


            // Lưu kết quả
            $results[] = [
                'id' => $item->id,
                'invoice_id' => $item->invoice_id,
                'product' => $item->product,
                'item_code' => $bestMatch,
                'meta' => [
                    'note' => $note,
                    'slug' => $slug,
                ],
            ];
        }

        return $results;
    }

    public function saveAutoFill(array $params): mixed
    {
        DB::beginTransaction();
        try {
            $rows = $params['items'];
            $year = $params['year'];
            foreach ($rows as $row) {
                $slug = $row['meta']['slug'];
                $invoiceDetail = InvoiceDetail::find($row['id']);
                if (!empty($invoiceDetail)) {
                    switch ($slug) {
                        case ItemCodeMetaSlugEnum::AUTO_FILL:
                            $codeId = $row['item_code']['id'] ?? null;
                            if (!empty($codeId)) {
                                $invoiceDetail->item_code_id = $codeId;
                                $invoiceDetail->save();
                            }
                            break;
                        case ItemCodeMetaSlugEnum::NEED_TO_CREATE:
                            $companyId = $invoiceDetail->invoice->company_id;
                            $itemCode = ItemCode::create([
                                'company_id' => $companyId,
                                'product_code' => $invoiceDetail->product,
                                'product' => $invoiceDetail->product,
                                'price' => $invoiceDetail->price,
                                'unit' => $invoiceDetail->unit,
                                'year' => $year,
                            ]);
                            if (empty($itemCode)) throw new Exception("Lỗi khi tạo mới mã: {$invoiceDetail->product}");
                            $invoiceDetail->item_code_id = $itemCode->id;
                            $invoiceDetail->save();
                            break;
                        case ItemCodeMetaSlugEnum::SAVED:
                            # code...
                            break;
                        default:
                            # code...
                            break;
                    }
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new CannotUpdateDBException(
                message: 'update: ' . $e->getMessage(),
                previous: $e
            );
        }
    }
}
