<?php

namespace App\Http\Requests\TaxFreeVoucherRecord;

use App\Http\Requests\BaseRequest;
use App\Rules\IsBase64Image;
use Illuminate\Foundation\Http\FormRequest;

class TaxFreeVoucherRecordUpdateRequest extends BaseRequest
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
            // 'tax_free_voucher_id' => ['required', 'integer', 'exists:tax_free_vouchers,id'],
            'company_detail_id' => ['required', 'integer', 'exists:company_details,id'],
            // 'user_id' => ['required', 'integer', 'exists:users,id'],
            'count_month' => ['required', 'numeric'],
            'start_month' => ['required', 'numeric'],
            'end_month' => ['required', 'numeric'],
        ];
    }
}
