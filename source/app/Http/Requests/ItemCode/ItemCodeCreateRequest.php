<?php

namespace App\Http\Requests\ItemCode;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemCodeCreateRequest extends BaseRequest
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
        $product_code = $this->input('product_code');
        $product_exchange = $this->input('product_exchange');
        $year = $this->input('year');
        return [
            'product_code' => [
                'required',
                'string',
                Rule::unique('item_codes')->where(function ($query) use ($company_id, $product_code, $product_exchange, $year) {
                    return $query->where([
                        ['company_id', $company_id],
                        ['product_code', $product_code],
                        // ['product_exchange', $product_exchange],
                        ['year', $year],
                    ]);
                })
            ],
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'product_exchange' => ['required', 'string'],
            'product' => ['nullable', 'string'],
            'unit' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'opening_balance_value' => ['numeric', 'min:0'], // auto compute (, 'gte:price')
            'year' => ['required', 'integer', 'digits:4', 'min:2000']
        ];
    }
}
