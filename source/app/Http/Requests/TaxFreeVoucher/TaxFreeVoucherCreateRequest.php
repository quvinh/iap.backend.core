<?php

namespace App\Http\Requests\TaxFreeVoucher;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class TaxFreeVoucherCreateRequest extends BaseRequest
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
            'name' => ['required', 'string', 'unique:tax_free_vouchers,name'],
            'number_account' => ['nullable', 'string'],
            'note' => ['nullable', 'string'],
        ];
    }
}
