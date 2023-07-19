<?php

namespace App\Http\Requests\CompanyDetail;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class CompanyDetailPropertyUpdateRequest extends BaseRequest
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
        $vouchers = $this->input('tax_free_vouchers');
        return [
            'company_type_id' => ['required', 'integer', 'exists:company_types,id'],
            'description' => ['required'],
            'arise_accounts' => ['array'],
            'tax_free_vouchers' => ['array'],
            'arise_accounts.*.id' => ['required', 'integer', 'exists:first_arise_accounts,id'],
            'arise_accounts.*.value_from' => ['required', 'numeric', 'min:0', 'max:100'],
            'arise_accounts.*.value_to' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
