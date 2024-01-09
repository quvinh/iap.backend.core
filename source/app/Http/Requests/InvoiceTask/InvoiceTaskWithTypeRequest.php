<?php

namespace App\Http\Requests\InvoiceTask;

use App\Helpers\Enums\InvoiceTypes;
use App\Helpers\Enums\TaskStatus;
use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvoiceTaskWithTypeRequest extends BaseRequest
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
            'task_id' => ['required', 'numeric', 'exists:invoice_tasks,id'],
            'type' => ['required', 'string', Rule::in(InvoiceTypes::getValues())],
        ];
    }
}
