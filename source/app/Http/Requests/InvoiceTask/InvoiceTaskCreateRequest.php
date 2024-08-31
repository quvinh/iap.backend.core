<?php

namespace App\Http\Requests\InvoiceTask;

use App\Helpers\Enums\TaskStatus;
use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvoiceTaskCreateRequest extends BaseRequest
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
        $company_id = $this->input('company_id');
        $month_of_year = $this->input('month_of_year');
        return [
            'company_id' => [
                'required',
                'integer',
                'exists:companies,id'
            ],
            'task_import' => ['nullable', 'string', Rule::in(TaskStatus::getValues())],
            'task_progress' => ['nullable', 'string', Rule::in(TaskStatus::getValues())],
            'month_of_year' => [
                'required',
                'string',
                'max:7',
                Rule::unique('invoice_tasks')->where(function ($query) use ($company_id, $month_of_year) {
                    return $query->where([
                        ['company_id', $company_id],
                        ['month_of_year', $month_of_year],
                    ]);
                })
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'month_of_year.unique' => 'Công việc tháng này đã tồn tại'
        ];
    }
}
