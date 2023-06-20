<?php

namespace App\DataResources\TaxFreeVoucher;

use App\DataResources\BaseDataResource;
use App\Models\TaxFreeVoucher;

class TaxFreeVoucherResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'name',
        'number_account',
        'note',
        'status',
        'created_at',
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return TaxFreeVoucher::class;
    }

    /**
     * Load data for output
     * @param TaxFreeVoucher $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);
    }
}
