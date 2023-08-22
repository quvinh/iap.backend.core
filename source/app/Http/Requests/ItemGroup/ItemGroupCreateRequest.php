<?php

namespace App\Http\Requests\ItemGroup;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemGroupCreateRequest extends BaseRequest
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
        $item_group = $this->input('code');
        return [
            'code' => [
                'required',
                'string',
                Rule::unique('item_groups')->where(function ($query) use ($company_id, $item_group) {
                    return $query->where([
                        ['company_id', $company_id],
                        ['code', $item_group],
                    ]);
                })
            ],
            'name' => ['required', 'string'],
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'item_codes' => ['array'],
            'item_codes.*.id' => ['required', 'integer', 'exists:item_codes,id'],
            'note' => ['nullable'],
            'year' => ['integer', 'digits:4', 'min:2000']
        ];
    }
}
