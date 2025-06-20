<?php

namespace App\Http\Requests\ItemCode;

use App\Http\Requests\DefaultSearchRequest;
use Illuminate\Foundation\Http\FormRequest;

class ItemCodeSearchRequest extends DefaultSearchRequest
{
    /**
     * Available relations to retrieve
     * @var string[]
     */
    protected array $relations = [
        'company',
        'item_group',
    ];

    protected array $fields = [
        'company_id',
        'year',
        'product_code',
        'product_exchange',
        'product',
        'unit',
        'price_from',
        'price_to',
        'status',
        'only_active',
        'search',
        'withs',
    ];

    /**
     * Overwrite this function to prepare or convert data before validating
     * @return void
     * @throws InvalidDatetimeInputException
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return parent::authorize();
    }

    /**
     * @return string[]
     */
    public function attributes(): array
    {
        return parent::attributes();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return parent::rules();
    }
}
