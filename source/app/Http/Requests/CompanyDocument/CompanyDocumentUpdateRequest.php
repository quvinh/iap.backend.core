<?php

namespace App\Http\Requests\CompanyDocument;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class CompanyDocumentUpdateRequest extends BaseRequest
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
            'company_id' => ['exists:companies,id'],
            'year' => ['integer', 'digits:4', 'min:2000'],
        ];
    }
}
