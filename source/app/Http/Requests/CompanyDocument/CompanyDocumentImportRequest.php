<?php

namespace App\Http\Requests\CompanyDocument;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class CompanyDocumentImportRequest extends BaseRequest
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
        $maxSizeUpload = config('upload.file.max_size_upload');
        return [
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'year' => ['required', 'max:4'],
            'file' => ['required', 'mimes:pdf,png,jpg,jpeg,doc,docx', "max:$maxSizeUpload"],
        ];
    }
}
