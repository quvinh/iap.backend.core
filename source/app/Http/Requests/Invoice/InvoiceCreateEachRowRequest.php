<?php

namespace App\Http\Requests\Invoice;

use App\Helpers\Enums\InvoiceTypes;
use App\Http\Requests\BaseRequest;
use App\Rules\ComparisonTotalMoney;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvoiceCreateEachRowRequest extends BaseRequest
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
        // $quantity = $this->input('quantity');
        // $price = $this->input('price');
        return [
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'type' => ['required', 'string', Rule::in(InvoiceTypes::getValues())],
            'date' => ['required', 'date_format:Y-m-d'],
            'partner_name' => ['string'],
            'partner_tax_code' => ['required', 'string', 'max:100'],
            'invoice_number' => ['required', 'integer'],
            'invoice_symbol' => ['required', 'string'],
            'product' => ['required', 'string'],
            'product_exchange' => ['nullable'],
            'unit' => ['required', 'string'],
            'vat' => ['required', 'integer'],
            'quantity' => ['required', 'numeric'],
            'price' => ['required', 'numeric'],
            // 'total_money' => ['required', 'numeric', new ComparisonTotalMoney($quantity, $price)],
        ];
    }
}
