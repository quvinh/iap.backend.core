<?php

namespace App\Http\Requests\InvoiceMedia;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class InvoiceMediaUpdateRequest extends BaseRequest
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
            'invoice_id' => ['integer', 'exists:invoices,id'],
            'year' => ['required', 'max:4'],
        ];
    }
}
