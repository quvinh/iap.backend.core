<?php

namespace App\Http\Requests\ItemCode;

use App\Http\Requests\BaseRequest;
use App\Rules\IsBase64Image;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemCodeUpdateRequest extends BaseRequest
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
        $item_group = $this->input('item_group');
        return [
            'item_group' => [
                'required',
                'string',
                Rule::unique('item_codes')->where(function ($query) use ($company_id, $item_group) {
                    return $query->where([
                        ['company_id', $company_id],
                        ['item_group', $item_group],
                    ]);
                })->ignore($id)
            ],
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'note' => ['nullable'],
        ];
    }
}
