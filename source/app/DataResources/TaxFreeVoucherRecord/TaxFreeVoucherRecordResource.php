<?php

namespace App\DataResources\TaxFreeVoucherRecord;

use App\DataResources\BaseDataResource;
use App\DataResources\User\UserResource;
use App\Models\TaxFreeVoucherRecord;

class TaxFreeVoucherRecordResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'tax_free_voucher_id',
        'company_detail_id',
        'user_id',
        'count_month',
        'start_month',
        'end_month',
        'json',
        'created_at',
        'created_by',
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return TaxFreeVoucherRecord::class;
    }

    /**
     * Load data for output
     * @param TaxFreeVoucherRecord $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);

        if (in_array('user', $this->fields)) {
            $this->withField('user');
            $this->user = new UserResource($obj->user);
        }
    }
}
