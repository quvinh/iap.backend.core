<?php

namespace App\Http\Requests\ItemGroup;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemGroupInsertRequest extends BaseRequest
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
            'item_group_id' => ['required', 'integer', 'exists:item_groups,id'],
            'item_codes' => ['array'],
            'item_codes.*.id' => ['required', 'integer', 'exists:item_codes,id'],
        ];
    }
}
