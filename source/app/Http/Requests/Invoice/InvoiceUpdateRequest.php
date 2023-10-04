<?php

namespace App\Http\Requests\Invoice;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\Enums\InvoiceTypes;
use Illuminate\Validation\Rule;

class InvoiceUpdateRequest extends BaseRequest
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
        $id = $this->input('id');
        $company_id = $this->input('company_id');
        return [
            'id' => ['exists:invoices,id'],
            'company_id' => ['exists:companies,id'],
            'type' => ['string', Rule::in(InvoiceTypes::getValues())],
            'date' => ['date_format:Y-m-d'],
            'partner_name' => ['string'],
            'partner_tax_code' => ['string', 'max:100'],
            'invoice_number' => ['numeric'],
            'invoice_symbol' => ['string'],

            'invoice_details' => ['array'],
            'invoice_details.*.invoice_id' => ['required', 'in:' . $id],
            'invoice_details.*.price' => ['required'],
            'invoice_details.*.product' => ['required'],
            'invoice_details.*.quantity' => ['required'],
            'invoice_details.*.total_money' => ['required'],
            'invoice_details.*.unit' => ['required'],
            'invoice_details.*.vat' => ['required'],
            'invoice_details.*.vat_money' => ['required'],
            'invoice_details.*.warehouse' => ['required'],
        ];
    }
}
