<?php

namespace App\Http\Requests\Invoice;

use App\Helpers\Enums\InvoiceTypes;
use App\Http\Requests\BaseRequest;
use App\Rules\ComparisonTotalMoney;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvoiceImportRequest extends BaseRequest
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
            'type' => ['required', 'string', 'max:10', Rule::in(InvoiceTypes::getValues())],
            'invoice_details' => ['array'],
            'invoice_details.*.date' => ['required', 'date_format:Y-m-d'],
            'invoice_details.*.partner_name' => ['string'],
            'invoice_details.*.partner_tax_code' => ['required', 'string', 'max:60'],
            'invoice_details.*.partner_address' => ['nullable'],
            'invoice_details.*.invoice_number' => ['required', 'integer'],
            'invoice_details.*.invoice_number_from' => ['integer'],
            'invoice_details.*.invoice_symbol' => ['required', 'string', 'max:20'],
            'invoice_details.*.product' => ['required', 'string'],
            'invoice_details.*.product_exchange' => ['nullable'],
            'invoice_details.*.unit' => ['required', 'string'],
            'invoice_details.*.vat' => ['required', 'integer'],
            'invoice_details.*.quantity' => ['required', 'numeric'],
            'invoice_details.*.price' => ['required', 'numeric'],
        ];
    }

    public function messages()
    {
        return [
            'company_id' => 'The company ID is required',
            'type' => 'The invoice type is required',
            'invoice_details' => 'The invoice details are required',
            'invoice_details.*.date' => 'The invoice date is required (:attribute)',
            'invoice_details.*.partner_name' => 'The partner name is required (:attribute)',
            'invoice_details.*.partner_tax_code' => 'The partner tax code is required (:attribute)',
            'invoice_details.*.partner_address' => 'The partner address is required (:attribute)',
            'invoice_details.*.invoice_number' => 'The invoice number is required (:attribute)',
            'invoice_details.*.invoice_number_from' => 'The invoice number from is required (:attribute)',
            'invoice_details.*.invoice_symbol' => 'The invoice symbol is required (:attribute)',
            'invoice_details.*.product' => 'The invoice product is required (:attribute)',
            'invoice_details.*.product_exchange' => 'The invoice product exchange is required (:attribute)',
            'invoice_details.*.unit' => 'The invoice unit is required (:attribute)',
            'invoice_details.*.vat' => 'The invoice VAT is required (:attribute)',
            'invoice_details.*.quantity' => 'The invoice quantity is required (:attribute)',
            'invoice_details.*.price' => 'The invoice price is required (:attribute)',
        ];
    }
}
