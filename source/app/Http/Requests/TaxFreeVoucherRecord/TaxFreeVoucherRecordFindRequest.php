<?php

namespace App\Http\Requests\TaxFreeVoucherRecord;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaxFreeVoucherRecordFindRequest extends BaseRequest
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
            'company_detail_id' => ['required', 'exists:company_details,id'],
            'start_month' => ['required', 'integer', 'min:1', 'max:12'],
            'end_month' => ['required', 'integer', 'required_with:start_month', 'gte:start_month', 'min:1', 'max:12'],
            'reset' => ['required', 'integer', Rule::in([0, 1])],
        ];
    }
}
