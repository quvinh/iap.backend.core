<?php

namespace App\Services\Company;

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
use App\Models\Company;
use App\Models\CompanyDetail;
use App\Repositories\Company\ICompanyRepository;
use App\Services\User\IUserService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class CompanyService extends \App\Services\BaseService implements ICompanyService
{
    private const DEFAULT_FOLDER_TO_UPLOAD_FILES = "images/companies";
    private ?ICompanyRepository $companyRepos = null;
    private ?IUserService $userService = null;

    public function __construct(ICompanyRepository $repos, IUserService $userService)
    {
        $this->companyRepos = $repos;
        $this->userService = $userService;
    }

    /**
     * Get single object of resources
     *
     * @param int $id
     * @param array<string> $withs
     * @return Company
     * @throws RecordIsNotFoundException
     */
    public function getSingleObject(int $id, array $withs = []): Company
    {
        try {
            $query = $this->companyRepos->queryOnAField(['id', $id]);
            $query = $this->companyRepos->with($withs, $query);
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
     * @return Collection<int,Company>
     * @throws ActionFailException
     * @throws Throwable
     */
    public function search(array $rawConditions, PaginationInfo &$paging = null, array $withs = []): Collection
    {
        try {
            $query = $this->companyRepos->search();

            if (isset($rawConditions['name'])) {
                $param = StringHelper::escapeLikeQueryParameter($rawConditions['name']);
                $query = $this->companyRepos->queryOnAField([DB::raw("upper(name)"), 'LIKE BINARY', DB::raw("upper(concat('%', ? , '%'))")], positionalBindings: ['name' => $param]);
            }

            if (isset($rawConditions['tax_code'])) {
                $param = $rawConditions['tax_code'];
                $query = $this->companyRepos->queryOnAField(['tax_code', '=', $param], $query);
            }

            if (isset($rawConditions['status'])) {
                $param = $rawConditions['status'];
                $query = $this->companyRepos->queryOnAField(['status', '=', $param], $query);
            }

            if (isset($rawConditions['updated_date'])) {
                $query = $this->companyRepos->queryOnDateRangeField($query, 'updated_at', $rawConditions['updated_date']);
            }
            if (isset($rawConditions['created_date'])) {
                $query = $this->companyRepos->queryOnDateRangeField($query, 'created_at', $rawConditions['created_date']);
            }

            # Query get companies authoritied
            $userId = auth()->user()->getAuthIdentifier();
            $userCompanies = $this->userService->findByCompanies($userId);
            if (empty($userCompanies)) {
                $query->whereIn('id', []);
            } else {
                $arr = array_map(function ($item) {
                    return $item['company_id'];
                }, $userCompanies);
                $query->whereIn('id', $arr);
            }

            $query = $this->companyRepos->with($withs, $query);


            if (!is_null($paging)) {
                $this->applyPagination($query, $paging);
            }

            if (isset($rawConditions['sort'])) {
                $sort = SortInfo::parse($rawConditions['sort']);
                return $this->companyRepos->sort($query, $sort)->get();
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
     * Get all companies
     * @return Collection
     */
    public function getAllCompanies(): Collection
    {
        try {
            $companies = $this->companyRepos->getAllCompanies();
            return $companies;
        } catch (\Exception $e) {
            throw new ActionFailException(
                previous: $e
            );
        }
    }

    /**
     * Create new item
     *
     * @param array $param
     * @param MetaInfo|null $commandMetaInfo
     * @return Company
     * @throws CannotSaveToDBException
     */
    public function create(array $param, MetaInfo $commandMetaInfo = null): Company
    {
        DB::beginTransaction();
        try {
            #1 Create
            $record = $this->companyRepos->create($param, $commandMetaInfo);
            // if (empty($record)) {
            //     throw new CannotSaveToDBException();
            // } else {
            //     $detail = new CompanyDetail();
            //     $detail->company_id = $record->id;
            //     $detail->company_type_id = $param['company_type_id'];
            //     $detail->description = isset($param['description']) ? $param['description'] : null;
            //     $detail->year = $param['year'];
            //     $detail->save();
            // }

            # Update file if needed
            if (isset($param['file_raw'])) {
                $rem = $record->registration_file ?? '';
                $file = StorageHelper::storageImage(self::DEFAULT_FOLDER_TO_UPLOAD_FILES, $param['file_raw'], StorageHelper::TMP_DISK_NAME, $rem);
                $record->registration_file = $file ?? null;
                $record->save();
            }

            # TODO: add company management
            $this->companyRepos->addUserCompany($record);

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
     * @return Company
     * @throws CannotUpdateDBException
     */
    public function update(int $id, array $param, MetaInfo $commandMetaInfo = null): Company
    {
        DB::beginTransaction();
        try {
            #1: Can edit? -> Yes: move to #2 No: return Exception with error
            $record = $this->companyRepos->getSingleObject($id)->first();
            if (empty($record)) {
                throw new RecordIsNotFoundException();
            }
            #2: update
            $param = array_merge($param, [
                'id' => $record->id
            ]);
            $record = $this->companyRepos->update($param, $commandMetaInfo);
            
            # Update file if needed
            if (isset($param['file_raw'])) {
                $rem = $record->registration_file ?? '';
                $file = StorageHelper::storageImage(self::DEFAULT_FOLDER_TO_UPLOAD_FILES, $param['file_raw'], StorageHelper::TMP_DISK_NAME, $rem);
                $record->registration_file = $file ?? null;
                $record->save();

                # Remove image from disk
                if (isset($rem)) StorageHelper::removeFile(StorageHelper::TMP_DISK_NAME, $rem);
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
            $record = $this->companyRepos->getSingleObject($id)->first();
            if (empty($record)) {
                throw new RecordIsNotFoundException();
            }
            $result =  $this->companyRepos->delete(id: $id, soft: $softDelete, meta: $commandMetaInfo);
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
     * Get list inventory by company
     */
    public function inventory(mixed $company_id, string $start, string $end): array
    {
        return $this->companyRepos->inventory($company_id, $start, $end);
    }
}
