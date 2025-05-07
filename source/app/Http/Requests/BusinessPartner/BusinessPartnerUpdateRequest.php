<?php

namespace App\Http\Requests\ItemGroup;

use App\Http\Requests\BaseRequest;
use App\Rules\IsBase64Image;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemGroupUpdateRequest extends BaseRequest
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
        $company_id = $this->input('company_id');
        $tax_code = $this->input('tax_code');
        return [
            'tax_code' => [
                'required',
                'string',
                Rule::unique('business_partners')->where(function ($query) use ($company_id, $tax_code) {
                    return $query->where([
                        ['company_id', $company_id],
                        ['tax_code', $tax_code],
                    ]);
                })->ignore($id)
            ],
            'name' => ['required', 'string'],
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'email' => ['nullable', 'email'],
        ];
    }
}
