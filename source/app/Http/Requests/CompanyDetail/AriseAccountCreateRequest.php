<?php

namespace App\Http\Requests\CompanyDetail;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AriseAccountCreateRequest extends BaseRequest
{
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
        $arise_account_id = $this->input('arise_account_id');
        return [
            'arise_account_id' => ['required', 'integer', 'exists:first_arise_accounts,id'],
            'value_from' => ['required', 'numeric'],
            'value_to' => ['required', 'numeric', 'required_with:value_from', 'gte:value_from'],
            'company_detail_id' => [
                'required', 
                'integer', 
                'exists:company_details,id',
                Rule::unique('company_detail_arise_accounts')->where(function($query) use($company_detail_id, $arise_account_id) {
                    return $query->where([
                        ['company_detail_id', $company_detail_id],
                        ['arise_account_id', $arise_account_id]
                    ]);
                })
            ],
        ];
    }
}
