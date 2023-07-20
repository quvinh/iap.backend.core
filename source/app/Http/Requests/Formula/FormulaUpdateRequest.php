<?php

namespace App\Http\Requests\Formula;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FormulaUpdateRequest extends BaseRequest
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
        $id = $this->id;
        $name = $this->input('name');
        $company_detail_id = $this->input('company_detail_id');
        return [
            'name' => [
                'required',
                'string',
                Rule::unique('formulas')->where(function ($query) use ($id, $name, $company_detail_id) {
                    return $query->where([
                        ['name', $name],
                        ['company_detail_id', $company_detail_id]
                    ]);
                })->ignore($id)
            ],
            'company_detail_id' => ['nullable', 'integer', 'exists:company_details,id'],
            'company_type_id' => ['nullable', 'integer', 'exists:company_types,id'],
            'note' => ['nullable', 'string'],
            'status' => ['nullable', 'integer']
        ];
    }
}
