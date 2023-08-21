<?php

namespace App\Http\Requests\ItemCode;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemCodeImportRequest extends BaseRequest
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
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'year' => ['required', 'integer', 'digits:4', 'min:2000'],
            'import' => ['required', 'array'],
            'import.*.product_code' => ['required', 'string'],
            // 'import.*.product_exchange' => ['string'],
            'import.*.unit' => ['required', 'string'],
            'import.*.quantity' => ['numeric', 'min:1'],
            'import.*.price' => ['required', 'numeric', 'min:1'],
            'import.*.opening_balance_value' => ['numeric', 'min:1'],
        ];
    }
}
