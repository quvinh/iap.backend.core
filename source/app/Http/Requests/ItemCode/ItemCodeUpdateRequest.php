<?php

namespace App\Http\Requests\ItemCode;

use App\Http\Requests\BaseRequest;
use App\Rules\IsBase64Image;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemCodeUpdateRequest extends BaseRequest
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
        $id = $this->id;
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
                        ['product_exchange', $product_exchange],
                        ['year', $year],
                    ]);
                })->ignore($id)
            ],
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'product_exchange' => ['required', 'string'],
            'product' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric'],
            'quantity' => ['nullable', 'numeric'],
            'begining_total_value' => ['nullable', 'numeric'],
            'year' => ['required', 'integer', 'digits:4', 'min:2000']
        ];
    }
}
