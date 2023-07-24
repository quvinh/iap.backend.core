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
            'type' => ['required', 'string', Rule::in(InvoiceTypes::getValues())],
            'invoice_details' => ['array'],
            'invoice_details.*.date' => ['required', 'date_format:Y-m-d'],
            'invoice_details.*.partner_name' => ['string'],
            'invoice_details.*.partner_tax_code' => ['required', 'string', 'max:100'],
            'invoice_details.*.invoice_number' => ['required', 'integer'],
            'invoice_details.*.invoice_number_from' => ['integer'],
            'invoice_details.*.invoice_symbol' => ['required', 'string'],
            'invoice_details.*.product' => ['required', 'string'],
            'invoice_details.*.unit' => ['required', 'string'],
            'invoice_details.*.vat' => ['required', 'integer'],
            'invoice_details.*.quantity' => ['required', 'numeric'],
            'invoice_details.*.price' => ['required', 'numeric'],
        ];
    }
}
