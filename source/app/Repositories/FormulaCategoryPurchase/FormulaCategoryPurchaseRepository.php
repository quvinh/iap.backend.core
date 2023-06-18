<?php

namespace App\Repositories\FormulaCategoryPurchase;

use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\IdIsNotProvidedException;
use App\Helpers\Common\MetaInfo;
use App\Models\FormulaCategoryPurchase;
use App\Repositories\BaseRepository;
use App\Exceptions\DB\RecordIsNotFoundException as DBRecordIsNotFoundException;
use function Spatie\SslCertificate\starts_with;

class FormulaCategoryPurchaseRepository extends BaseRepository implements IFormulaCategoryPurchaseRepository
{
    /**
     * get corresponding model class name
     * @return string
     */
    function getRepositoryModelClass(): string
    {
        return FormulaCategoryPurchase::class;
    }

    /**
     * Try to create the object using the given info
     * @param array<string, mixed> $form
     * @param MetaInfo|null $meta
     * @param string $idColumnName
     * @return FormulaCategoryPurchase
     * @throws CannotSaveToDBException
     */
    public function create(array $form, MetaInfo $meta = null, string $idColumnName = 'id'): FormulaCategoryPurchase
    {
        $entity = new FormulaCategoryPurchase();
        $entity->fill($form);
        $entity->setFormulaCategoryPurchase($form['value_from'], $form['value_to']);
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
    function update(array $form, MetaInfo $meta = null, string $idColumnName = 'id'): FormulaCategoryPurchase
    {
        if (!in_array('id', array_keys($form))) throw new IdIsNotProvidedException();

        $entity = $this->getSingleObject($form[$idColumnName], $idColumnName)->first();
        if (isset($entity)) {
            $entity->fill($form);
            $entity->setFormulaCategoryPurchase($form['value_from'], $form['value_to']);
            if ($entity->save() !== false) {
                return $entity;
            } else {
                throw new CannotSaveToDBException();
            }
        }
        throw new DBRecordIsNotFoundException();
    }
}
