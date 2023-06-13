<?php

namespace App\Http\Requests\CompanyDetail;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyDetailCreateRequest extends BaseRequest
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
        $company_id = $this->input('company_id');
        $year = $this->input('year');
        return [
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'company_type_id' => ['required', 'integer', 'exists:company_types,id'],
            'description' => ['nullable'],
            'year' => [
                'required', 
                'integer', 
                'digits:4', 
                'min:2000',
                Rule::unique('company_details')->where(function($query) use($company_id, $year) {
                    return $query->where([
                        ['company_id', $company_id],
                        ['year', $year]
                    ]);
                })]
        ];
    }
}
