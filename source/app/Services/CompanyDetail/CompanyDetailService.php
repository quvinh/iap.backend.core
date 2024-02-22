<?php

namespace App\Services\CompanyDetail;

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
use App\Models\CompanyDetail;
use App\Repositories\CompanyDetail\ICompanyDetailRepository;
use App\Repositories\FirstAriseAccount\IFirstAriseAccountRepository;
use App\Repositories\TaxFreeVoucher\ITaxFreeVoucherRepository;
use App\Services\User\IUserService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class CompanyDetailService extends \App\Services\BaseService implements ICompanyDetailService
{
    private ?ICompanyDetailRepository $companyDetailRepos = null;
    private ?IFirstAriseAccountRepository $ariseAccountRepos = null;
    private ?ITaxFreeVoucherRepository $taxFreeVoucherRepos = null;
    private ?IUserService $userService = null;

    public function __construct(
        ICompanyDetailRepository $repos,
        IFirstAriseAccountRepository $ariseAccountRepos,
        ITaxFreeVoucherRepository $taxFreeVoucherRepos,
        IUserService $userService
    ) {
        $this->companyDetailRepos = $repos;
        $this->ariseAccountRepos = $ariseAccountRepos;
        $this->taxFreeVoucherRepos = $taxFreeVoucherRepos;
        $this->userService = $userService;
    }

    /**
     * Get single object of resources
     *
     * @param int $id
     * @param array<string> $withs
     * @return CompanyDetail
     * @throws RecordIsNotFoundException
     */
    public function getSingleObject(int $id, array $withs = []): CompanyDetail
    {
        try {
            $query = $this->companyDetailRepos->queryOnAField(['id', $id]);
            $query = $this->companyDetailRepos->with($withs, $query);
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
     * @return Collection<int,CompanyDetail>
     * @throws ActionFailException
     * @throws Throwable
     */
    public function search(array $rawConditions, PaginationInfo &$paging = null, array $withs = []): Collection
    {
        try {
            $query = $this->companyDetailRepos->search();

            if (isset($rawConditions['id'])) {
                $param = $rawConditions['id'];
                $query = $this->companyDetailRepos->queryOnAField(['id', '=', $param], $query);
            }

            if (isset($rawConditions['year'])) {
                $param = $rawConditions['year'];
                $query = $this->companyDetailRepos->queryOnAField(['year', '=', $param], $query);
            }

            if (isset($rawConditions['company_id'])) {
                $param = $rawConditions['company_id'];
                $query = $this->companyDetailRepos->queryOnAField(['company_id', '=', $param], $query);
            }

            if (isset($rawConditions['updated_date'])) {
                $query = $this->companyDetailRepos->queryOnDateRangeField($query, 'updated_at', $rawConditions['updated_date']);
            }
            if (isset($rawConditions['created_date'])) {
                $query = $this->companyDetailRepos->queryOnDateRangeField($query, 'created_at', $rawConditions['created_date']);
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

            $query = $this->companyDetailRepos->with($withs, $query);


            if (!is_null($paging)) {
                $this->applyPagination($query, $paging);
            }

            if (isset($rawConditions['sort'])) {
                $sort = SortInfo::parse($rawConditions['sort']);
                return $this->companyDetailRepos->sort($query, $sort)->get();
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
     * @return CompanyDetail
     * @throws CannotSaveToDBException
     */
    public function create(array $param, MetaInfo $commandMetaInfo = null): CompanyDetail
    {
        DB::beginTransaction();
        try {
            #1 Create
            $record = $this->companyDetailRepos->create($param, $commandMetaInfo);
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
     * @return CompanyDetail
     * @throws CannotUpdateDBException
     */
    public function update(int $id, array $param, MetaInfo $commandMetaInfo = null): CompanyDetail
    {
        DB::beginTransaction();
        try {
            #1: Can edit? -> Yes: move to #2 No: return Exception with error
            $record = $this->companyDetailRepos->getSingleObject($id)->first();
            if (empty($record)) {
                throw new RecordIsNotFoundException();
            }
            #2: update
            $param = array_merge($param, [
                'id' => $record->id
            ]);
            $record = $this->companyDetailRepos->update($param, $commandMetaInfo);
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
            $record = $this->companyDetailRepos->getSingleObject($id)->first();
            if (empty($record)) {
                throw new RecordIsNotFoundException();
            }
            $result =  $this->companyDetailRepos->delete(id: $id, soft: $softDelete, meta: $commandMetaInfo);
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
     * Create company_detail_arise_accout
     * @param array $param
     */
    public function createAriseAccount(array $param): Model
    {
        DB::beginTransaction();
        try {
            $record = $this->companyDetailRepos->createAriseAccount($param);
            DB::commit();
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
     * Update company_detail_arise_accout
     * @param array $param
     * @param mixed $id
     */
    public function updateAriseAccount(mixed $id, array $param): Model
    {
        DB::beginTransaction();
        try {
            $param = array_merge($param, [
                'id' => $id
            ]);
            $record = $this->companyDetailRepos->updateAriseAccount($param);
            DB::commit();
            return $record;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new CannotUpdateDBException(
                message: 'update: ' . json_encode(['param' => $param]),
                previous: $e
            );
        }
    }

    /**
     * Delete company_detail_arise_accout
     * @param mixed $id
     */
    // public function deleteAriseAccount(mixed $id): bool
    // {
    //     DB::beginTransaction();
    //     try {
    //         $record = $this->companyDetailRepos->deleteAriseAccount($id);
    //         DB::commit();
    //         return $record;
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         throw new CannotDeleteDBException(
    //             message: 'delete: ' . json_encode(['id' => $id]),
    //             previous: $e
    //         );
    //     }
    // }

    /**
     * Update company detail property
     * @param array $param
     * @param mixed $id
     */
    public function updateProperties(mixed $id, array $param, MetaInfo $commandMetaInfo = null): CompanyDetail
    {
        DB::beginTransaction();
        try {
            $entity = $this->companyDetailRepos->getSingleObject($id)->first();
            if (!$entity) throw new RecordIsNotFoundException(message: "Company detail not found with ID:$id");
            # TODO: first arise accounts
            $this->companyDetailRepos->deleteAriseAccount($entity->id, array_map(function ($value) {
                return $value['id'];
            }, $param['arise_accounts']));
            foreach ($param['arise_accounts'] as $acc) {
                $account = $this->ariseAccountRepos->getSingleObject($acc['id'])->first();
                if (!$account) throw new RecordIsNotFoundException(message: "First arise account not found with ID:" . $acc['id']);
                $ckAccount = $this->companyDetailRepos->getSinglePropertyObject($entity->id, $account->id)->first();
                if ($ckAccount) {
                    # Update info
                    $this->companyDetailRepos->updateAriseAccount([
                        'id' => $ckAccount->id,
                        'company_detail_id' => $entity->id,
                        'arise_account_id' => $ckAccount->id,
                        'value_from' => $acc['value_from'],
                        'value_to' => $acc['value_to'],
                    ]);
                } else {
                    # Create info
                    $this->companyDetailRepos->createAriseAccount([
                        'company_detail_id' => $entity->id,
                        'arise_account_id' => $account->id,
                        'value_from' => $acc['value_from'],
                        'value_to' => $acc['value_to'],
                    ]);
                }
            }
            # TODO: tax free vouchers 
            $this->companyDetailRepos->deleteTaxFreeVoucher($entity->id, $param['tax_free_vouchers']);
            foreach ($param['tax_free_vouchers'] as $idT) {
                $tax = $this->taxFreeVoucherRepos->getSingleObject($idT)->first();
                // if (!$tax) throw new RecordIsNotFoundException(message: "Tax free voucher not found with ID: $idT");
                if (!empty($tax)) {
                    $ckTax = $this->companyDetailRepos->getSingleVoucherPropertyObject($entity->id, $tax->id)->first();
                    if (!empty($ckTax)) {
                        $this->companyDetailRepos->createTaxFreeVoucher([
                            'company_detail_id' => $entity->id,
                            'tax_free_voucher_id' => $tax->id,
                        ]);
                    }
                }
            }

            $record = $this->companyDetailRepos->update([
                'id' => $entity->id,
                'company_type_id' => $param['company_type_id'],
                'description' => $param['description'],
            ], $commandMetaInfo);
            DB::commit();
            return $record;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ActionFailException(
                message: $e->getMessage(),
                previous: $e
            );
        }
    }
}
