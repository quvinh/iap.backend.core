<?php

namespace App\Http\Requests\Invoice;

use App\Helpers\Enums\InvoiceTypes;
use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvoiceFindNextRequest extends BaseRequest
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
            'invoice_id' => ['required'],
            'company_id' => ['required', 'exists:companies,id'],
            'type' => ['required', 'string', Rule::in(InvoiceTypes::getValues())],
            'operate' => ['required', 'string', Rule::in(['<=', '>='])],
        ];
    }
}
