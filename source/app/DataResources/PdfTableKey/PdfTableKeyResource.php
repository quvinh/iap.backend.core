<?php

namespace App\DataResources\PdfTableKey;

use App\DataResources\BaseDataResource;
use App\Models\PdfTableKey;

class PdfTableKeyResource extends BaseDataResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
        'key',
        'amount',
        'email',
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return PdfTableKey::class;
    }

    /**
     * Load data for output
     * @param PdfTableKey $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);
    }
}
