<?php

namespace App\Http\Requests\Formula;

use App\Http\Requests\DefaultSearchRequest;
use Illuminate\Foundation\Http\FormRequest;

class FormulaSearchRequest extends DefaultSearchRequest
{
    /**
     * Available relations to retrieve
     * @var string[]
     */
    protected array $relations = [
        'company_detail',
        'company_type',
        'category_purchases',
        'category_solds',
        'commodities',
        'materials',
    ];

    protected array $fields = [         
        'withs',
        'company_detail_id',
        'id',
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
