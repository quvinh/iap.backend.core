<?php

namespace App\Repositories\ItemCode;

use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\IdIsNotProvidedException;
use App\Helpers\Common\MetaInfo;
use App\Models\ItemCode;
use App\Repositories\BaseRepository;
use App\Exceptions\DB\RecordIsNotFoundException as DBRecordIsNotFoundException;
use Illuminate\Database\Eloquent\Builder;

use function Spatie\SslCertificate\starts_with;

class ItemCodeRepository extends BaseRepository implements IItemCodeRepository
{
    /**
     * get corresponding model class name
     * @return string
     */
    function getRepositoryModelClass(): string
    {
        return ItemCode::class;
    }

    /**
     * Try to create the object using the given info
     * @param array<string, mixed> $form
     * @param MetaInfo|null $meta
     * @param string $idColumnName
     * @return ItemCode
     * @throws CannotSaveToDBException
     */
    public function create(array $form, MetaInfo $meta = null, string $idColumnName = 'id'): ItemCode
    {
        $entity = new ItemCode();
        $entity->fill($form);
        if (!isset($form['opening_balance_value'])) {
            $entity->setItemCode($form['quantity'] ?? 1, $form['price']);
        }
        $entity->setMetaInfo($meta, true);
        $chk = $entity->save();
        if ($chk) {
            return $entity;
        } else {
            throw new CannotSaveToDBException();
        }
    }

    /**
     * Try to save the object using the given info
     * @param array<string, mixed> $form
     * @param MetaInfo|null $meta
     * @param string $idColumnName
     * @return mixed
     * @throws CannotSaveToDBException
     * @throws IdIsNotProvidedException
     * @throws DBRecordIsNotFoundException
     */
    function update(array $form, MetaInfo $meta = null, string $idColumnName = 'id'): ItemCode
    {
        if (!in_array('id', array_keys($form))) throw new IdIsNotProvidedException();

        $entity = $this->getSingleObject($form[$idColumnName], $idColumnName)->first();
        if (isset($entity)) {
            $entity->fill($form);
            if (!isset($form['opening_balance_value'])) {
                $entity->setItemCode($form['quantity'] ?? $entity->quantity, $form['price']);
            }
            $entity->setMetaInfo($meta, false);
            if ($entity->save()) {
                return $entity;
            } else {
                throw new CannotSaveToDBException();
            }
        }
        throw new DBRecordIsNotFoundException();
    }

    /**
     * Find by group
     * @param $group
     * @return Builder
     */
    public function findByGroup($group): Builder | null
    {
        return ItemCode::query()->where('item_group_id', $group);
    }

    public function getAll(array $params): Builder | null
    {
        return ItemCode::query()->where([
            ['company_id', $params['company_id']],
            ['year', $params['year']],
        ]);
    }
}
