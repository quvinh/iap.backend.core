<?php

namespace App\Http\Requests\Company;

use App\Http\Requests\DefaultSearchRequest;
use Illuminate\Foundation\Http\FormRequest;

class CompanySearchRequest extends DefaultSearchRequest
{
    /**
     * Available relations to retrieve
     * @var string[]
     */
    protected array $relations = [
        'types',
        'years',
        'documents',
        'contract',
        'users',
        'userAssignments',
    ];

    protected array $fields = [
        'withs',
        'status',
        'tax_code',
        'business_object',
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
