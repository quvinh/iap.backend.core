<?php

namespace App\Repositories\User;

use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\IdIsNotProvidedException;
use App\Helpers\Common\MetaInfo;
use App\Models\User;
use App\Repositories\BaseRepository;
use App\Exceptions\DB\RecordIsNotFoundException as DBRecordIsNotFoundException;
use function Spatie\SslCertificate\starts_with;

class UserRepository extends BaseRepository implements IUserRepository {
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
    public function findByEmail($email): User
    {
        $email = strtolower(''.$email);
        return User::query()->where('email', $email)->first();
    }

    /**
     * Find user by his/her code
     * @param $code
     * @return User|null
     */
    public function findByCode($code): User|null
    {
        $code = strtolower(''.$code);
        return User::query()->where('code', $code)->first();
    }

    /**
     * Find user by his/her phone
     * @param $phone
     * @return User|null
     */
    public function findByPhone($phone): User|null
    {
        $phone = strtolower(''.$phone);
        // Only allow VIETNAMESE PHONE

        // if (starts_with($phone, '+84')) $phone = substr($phone, 3);

        // if (! starts_with($phone, '0')) $phone = '0'.$phone;

        return User::query()->where('phone', $phone)->first();
    }

    /**
     * update
     */
    public function update(array $form, ?MetaInfo $meta = null, string $idColumnName = 'id'): mixed
    {
        if (!in_array('id', array_keys($form))) throw new IdIsNotProvidedException();

        $entity = $this->getSingleObject($form[$idColumnName], $idColumnName);
        if (isset($entity)) {
            $entity->fill($form);
            // $entity->setMetaInfo($meta, false);
            if ($entity->save() !== false) {
                return $entity;
            } else {
                throw new CannotSaveToDBException();
            }
        }
        throw new DBRecordIsNotFoundException();
    }

    /**
     * change password
     */
    public function changePassword(array $form, ?MetaInfo $meta = null, string $idColumnName = 'id'): mixed
    {
        if (!in_array('id', array_keys($form))) throw new IdIsNotProvidedException();

        $entity = $this->getSingleObject($form[$idColumnName], $idColumnName);
        if (isset($entity)) {
            $isExcecuted = User::whereId($form[$idColumnName])->update(['password' => $form['password']]);
            if ($isExcecuted !== false) {
                return $entity;
            } else {
                throw new CannotSaveToDBException();
            }
        }
        throw new DBRecordIsNotFoundException();
    }
}
