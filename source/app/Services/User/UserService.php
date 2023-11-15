<?php

namespace App\Services\User;

use App\DataResources\PaginationInfo;
use App\DataResources\SortInfo;
use App\Exceptions\Business\ActionFailException;
use App\Exceptions\DB\CannotDeleteDBException;
use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\CannotUpdateDBException;
use App\Exceptions\DB\RecordIsNotFoundException;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\UserRoles;
use App\Helpers\Utils\StorageHelper;
use App\Helpers\Utils\StringHelper;
use App\Jobs\ForgotPasswordJob;
use App\Models\User;
use App\Models\UserCompany;
use App\Models\UserDetail;
use App\Repositories\Company\ICompanyRepository;
use App\Repositories\User\IUserDetailRepository;
use App\Repositories\User\IUserRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

class UserService extends \App\Services\BaseService implements IUserService
{
    private ?IUserRepository $userRepos = null;
    private ?ICompanyRepository $companyRepos = null;
    private const DEFAULT_FOLDER_TO_UPLOAD_IMAGES = "images/users";

    public function __construct(IUserRepository $repos, ICompanyRepository $companyRepos)
    {
        $this->userRepos = $repos;
        $this->companyRepos = $companyRepos;
    }

    /**
     * Get single object of resources
     *
     * @param int $id
     * @param array<string> $withs
     * @return User
     * @throws RecordIsNotFoundException
     */
    public function getSingleObject(int $id, array $withs = []): User
    {
        try {
            $query = $this->userRepos->queryOnAField(['id', $id]);
            $query = $this->userRepos->with($withs, $query);
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
     * @return Collection<int,User>
     * @throws ActionFailException
     * @throws Throwable
     */
    public function search(array $rawConditions, PaginationInfo &$paging = null, array $withs = []): Collection
    {
        try {
            $query = $this->userRepos->search();
            if (isset($rawConditions['name'])) {
                $param = StringHelper::escapeLikeQueryParameter($rawConditions['name']);
                $query = $this->userRepos->queryOnAField([DB::raw("upper(name)"), 'LIKE BINARY', DB::raw("upper(concat('%', ? , '%'))")], positionalBindings: ['name' => $param]);
            }

            if (isset($rawConditions['updated_date'])) {
                $query = $this->userRepos->queryOnDateRangeField($query, 'updated_at', $rawConditions['updated_date']);
            }
            if (isset($rawConditions['created_date'])) {
                $query = $this->userRepos->queryOnDateRangeField($query, 'updated_at', $rawConditions['created_date']);
            }

            $query = $this->userRepos->with($withs, $query);


            if (!is_null($paging)) {
                $this->applyPagination($query, $paging);
            }

            if (isset($rawConditions['sort'])) {
                $sort = SortInfo::parse($rawConditions['sort']);
                return $this->userRepos->sort($query, $sort)->get();
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
     * @return User
     * @throws CannotSaveToDBException
     */
    public function create(array $param, MetaInfo $commandMetaInfo = null): User
    {
        DB::beginTransaction();
        try {
            #1 Register new user
            $user = new User();
            $user->name = $param['name'];
            $user->username = $param['username'] ?? null;
            $user->email = $param['email'] ?? null;
            $user->email_verified_at = Carbon::now();
            $user->phone = $param['phone'] ?? null;
            $user->birthday = isset($param['birthday']) ? Carbon::parse($param['birthday']) : null;
            $user->phone = $param['address'] ?? null;
            $user->role_id = $param['role_id'] ?? UserRoles::MODERATOR;
            $user->password = Hash::make(md5('password')); // password default
            $user->save();
            #2 Check have companies
            if (isset($param['company_id'])) {
                foreach ($param['company_id'] as $company_id) {
                    $company = $this->companyRepos->getSingleObject($company_id)->first();
                    if(empty($company)) {
                        throw new CannotSaveToDBException(message: "company with id:$company_id not exists");
                    } else {
                        $userCompany = new UserCompany();
                        $userCompany->user_id = $user->id;
                        $userCompany->company_id = $company_id;
                        $userCompany->save();
                    }
                }
            }
            DB::commit();
            #3 Return User
            return $user;
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
     * @return User
     * @throws CannotUpdateDBException
     */
    public function update(int $id, array $param, MetaInfo $commandMetaInfo = null): User
    {
        DB::beginTransaction();
        try {
            #1: Can edit? -> Yes: move to #2 No: return Exception with error
            $record = $this->userRepos->getSingleObject($id)->first();
            if (empty($record)) {
                throw new RecordIsNotFoundException();
            }
            #2: update
            $param = array_merge($param, [
                'id' => $record->id
            ]);
            $record = $this->userRepos->update($param, $commandMetaInfo);
            // update photo if needed
            if (isset($param['photo_raw'])) {
                $rem = $record->photo ?? '';
                $photo = StorageHelper::storageImage(
                    self::DEFAULT_FOLDER_TO_UPLOAD_IMAGES, 
                    $param['photo_raw'], 
                    StorageHelper::TMP_DISK_NAME, 
                    $rem);
                $record->photo = $photo ?? null;
                $record->save();
            }
            #3 Update user companies
            if (isset($param['company_id'])) {
                UserCompany::where('user_id', $record->id)->delete();
                foreach ($param['company_id'] as $company_id) {
                    $company = $this->companyRepos->getSingleObject($company_id)->first();
                    if(empty($company)) {
                        throw new CannotSaveToDBException(message: "company with id:$company_id not exists");
                    } else {
                        $userCompany = new UserCompany();
                        $userCompany->user_id = $record->id;
                        $userCompany->company_id = $company_id;
                        $userCompany->save();
                    }
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
            $record = $this->userRepos->getSingleObject($id)->first();
            if (empty($record)) {
                throw new RecordIsNotFoundException();
            }
            $result =  $this->userRepos->delete(id: $id, soft: $softDelete, meta: $commandMetaInfo);
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

    public function findByUsername($username): User|null
    {
        return $this->userRepos->findByUsername($username);
    }

    public function findByEmail($email): User|null
    {
        return $this->userRepos->findByEmail($email);
    }

    public function findByCompanies($user_id): mixed
    {
        return $this->userRepos->findByCompanies($user_id);
    }

    /**
     * Change password
     */
    public function changePassword(int $id, array $param, MetaInfo $commandMetaInfo = null): User | null
    {
        try {
            $record = $this->userRepos->getSingleObject($id)->first();
            if (empty($record)) {
                throw new RecordIsNotFoundException();
            }
            # change password
            if (!Hash::check($param['old_password'], $record->password)) {
                throw new ActionFailException(
                    message: 'change password failed'
                );
            }
            $param = array_merge([
                'id' => $record->id,
                'password' => Hash::make($param['new_password'])
            ]);
            $record = $this->userRepos->changePassword($param, $commandMetaInfo);
            return $record;
        } catch (\Exception $e) {
            throw new ActionFailException(
                message: 'change password failed',
                previous: $e
            );
        }
    }

    /**
     * Forgot password
     */
    public function forgotPassword(string $email): User | null
    {
        try {
            $random = rand(10000, 99999);
            $newPassword = "IAP$random@";
            $record = $this->userRepos->findByEmail($email);
            if (empty($record)) {
                throw new RecordIsNotFoundException(message: 'Email not found');
            }
            $record->password = Hash::make($newPassword);
            if ($record->save()) {
                Log::channel('forgot_password')->info($record->username . ' reset password', ['name' => $record->name, 'email' => $record->email]);
                ForgotPasswordJob::dispatch($record, $newPassword)->delay(now()->addMinute(1));
            } else {
                throw new CannotUpdateDBException('Cannot update DB');
            }
            return $record;
        } catch (\Exception $e) {
            throw new ActionFailException(
                message: $e->getMessage(),
                previous: $e
            );
        }
    }

    /**
     * Amount users
     */
    public function getAllUsers(): Collection
    {
        return $this->userRepos->getAllUsers();
    }
}
