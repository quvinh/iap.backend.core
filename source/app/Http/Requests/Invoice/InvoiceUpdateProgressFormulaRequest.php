<?php

namespace App\Http\Requests\Invoice;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class InvoiceUpdateProgressFormulaRequest extends BaseRequest
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
            'invoice_task_id' => ['required', 'integer', 'exists:invoice_tasks,id'],
            'invoice_details' => ['required', 'array'],
            'invoice_details.*.invoice_detail_id' => ['required', 'integer'],
            'invoice_details.*.formula_path_id' => ['required', 'string'],
            'invoice_details.*.warehouse' => ['required', 'integer'],
            'invoice_details.*.formula_commodity_id' => ['nullable', 'integer'],
            'invoice_details.*.formula_material_id' => ['nullable', 'integer'],
        ];
    }
}
