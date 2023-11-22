<?php

namespace App\DataResources\CompanyDetail;

use App\DataResources\BaseDataResource;
use App\Models\OpeningBalanceVat;

class OpeningBalanceVatResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'company_detail_id',
        'count_month',
        'start_month',
        'end_month',
        'money',
        'meta',
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return OpeningBalanceVat::class;
    }

    /**
     * Load data for output
     * @param OpeningBalanceVat $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);
    }
}
