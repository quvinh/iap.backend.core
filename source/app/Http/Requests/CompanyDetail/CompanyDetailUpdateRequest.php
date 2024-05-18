<?php

namespace App\Http\Requests\CompanyDetail;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class CompanyDetailUpdateRequest extends BaseRequest
{
    /**
     * Available relations to retrieve
     * @var string[]
     */
    protected array $relations = [];

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'company_id' => ['integer', 'exists:companies,id'],
            'company_type_id' => ['integer', 'exists:company_types,id'],
            'year' => ['integer', 'digits:4', 'min:2000'],
            'description' => ['nullable']
        ];
    }
}
