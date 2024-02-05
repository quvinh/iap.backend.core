<?php

namespace App\Repositories\User;

use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\IdIsNotProvidedException;
use App\Helpers\Common\MetaInfo;
use App\Models\User;
use App\Repositories\BaseRepository;
use App\Exceptions\DB\RecordIsNotFoundException as DBRecordIsNotFoundException;
use App\Models\UserCompany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

use function Spatie\SslCertificate\starts_with;

class UserRepository extends BaseRepository implements IUserRepository
{
    /**
     * get corresponding model class name
     * @return string
     */
    function getRepositoryModelClass(): string
    {
        return User::class;
    }

    /**
     * Find user by his/her email
     * @param $email
     * @return User
     */
    public function findByEmail($email): User | null
    {
        $email = strtolower('' . $email);
        return User::query()->where('email', $email)->first();
    }

    /**
     * Find user by his/her username
     * @param $username
     * @return User
     */
    public function findByUsername($username): User | null
    {
        return User::query()->where('username', $username)->first();
    }

    /**
     * Find companies
     * @param $user_id
     * @return User
     */
    public function findByCompanies($user_id): mixed
    {
        return UserCompany::query()->where('user_id', $user_id)->get('company_id')->toArray();
    }

    /**
     * change password
     */
    public function changePassword(array $form, ?MetaInfo $meta = null, string $idColumnName = 'id'): User | null
    {
        $user = User::find(auth()->user()->getAuthIdentifier());
        if (isset($user) && isset($form['password'])) {
            $user->password = $form['password'];
            if ($user->save()) return $user;
        }
        throw new DBRecordIsNotFoundException();
    }

    /**
     * Amount users
     */
    public function getAllUsers(): Collection
    {
        return User::all();
    }
}
