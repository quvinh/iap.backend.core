<?php

namespace App\Http\Requests\Formula;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FormulaCreateRequest extends BaseRequest
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
        $name = $this->input('name');
        $company_detail_id = $this->input('company_detail_id');
        return [
            'name' => [
                'required',
                'string',
                Rule::unique('formulas')->where(function ($query) use ($name, $company_detail_id) {
                    return $query->where([
                        ['name', $name],
                        ['company_detail_id', $company_detail_id]
                    ]);
                })
            ],
            'company_detail_id' => ['required', 'integer', 'exists:company_details,id'],
            'company_type_id' => ['required', 'integer', 'exists:company_types,id'],
            'note' => ['nullable', 'string']
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Tên công thức',
            'company_detail_id' => 'Doanh nghiệp',
            'company_type_id' => 'Loại hình',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập :attribute',
            'company_detail_id.required' => 'Vui lòng chọn :attribute',
            'company_type_id.required' => 'Vui lòng chọn :attribute',
        ];
    }
}
