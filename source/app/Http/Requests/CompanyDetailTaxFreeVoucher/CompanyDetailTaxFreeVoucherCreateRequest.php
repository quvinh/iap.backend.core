<?php

namespace App\Http\Requests\CompanyDetailTaxFreeVoucher;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyDetailTaxFreeVoucherCreateRequest extends BaseRequest
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
        $company_detail_id = $this->input('company_detail_id');
        $tax_free_voucher_id = $this->input('tax_free_voucher_id');
        return [
            'company_detail_id' => [
                'required',
                'integer',
                'exists:company_details,id',
                Rule::unique('company_detail_tax_free_vouchers')->where(function ($query) use ($company_detail_id, $tax_free_voucher_id) {
                    return $query->where([
                        ['company_detail_id', $company_detail_id],
                        ['tax_free_voucher_id', $tax_free_voucher_id],
                    ]);
                })
            ],
            'tax_free_voucher_id' => ['required', 'integer', 'exists:tax_free_vouchers,id'],
        ];
    }
}
