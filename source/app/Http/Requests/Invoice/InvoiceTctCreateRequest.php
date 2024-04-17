<?php

namespace App\Http\Requests\Invoice;

use App\Helpers\Enums\InvoiceProperties;
use App\Helpers\Enums\InvoiceTypes;
use App\Http\Requests\BaseRequest;
use App\Rules\ComparisonTotalMoney;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvoiceTctCreateRequest extends BaseRequest
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
            'date' => ['required', 'date_format:Y-m-d'],
            'partner_name' => ['string'],
            'partner_tax_code' => ['required', 'string', 'max:100'],
            'partner_address' => ['nullable'],
            'invoice_number' => ['required', 'integer'],
            'invoice_number_from' => ['integer'],
            'invoice_symbol' => ['required', 'string', 'max:20'],
            'type' => ['required', 'string', 'max:10', Rule::in(InvoiceTypes::getValues())],
            'property' => ['numeric', Rule::in(InvoiceProperties::getValues())],
            'invoice_details' => ['array'],
            'invoice_details.*.product' => ['required', 'string'],
            'invoice_details.*.product_exchange' => ['nullable'],
            'invoice_details.*.unit' => ['required', 'string'],
            'invoice_details.*.vat' => ['required', 'numeric'],
            'invoice_details.*.vat_money' => ['required', 'numeric'],
            'invoice_details.*.quantity' => ['required', 'numeric'],
            'invoice_details.*.price' => ['required', 'numeric'],
            'invoice_details.*.total_money' => ['required', 'numeric'],
        ];
    }
}
