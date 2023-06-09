<?php

namespace App\Http\Requests\Company;

use App\Http\Requests\BaseRequest;
use App\Rules\IsBase64Image;
use Illuminate\Foundation\Http\FormRequest;

class CompanyUpdateRequest extends BaseRequest
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
            'name' => ['required', 'string'],
            'tax_code' => ['required', 'min:100', 'string', 'unique:companies,tax_code'],
            'tax_password' => ['nullable', 'string'],
            'email' => ['nullable', 'string', 'email'],
            'phone' => ['nullable', 'string', 'min:10', 'max:20'],
            'logo' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'manager_name' => ['nullable', 'string'],
            'manager_role' => ['nullable', 'string'],
            'manager_phone' => ['nullable', 'string'],
            'email' => ['nullable', 'string'],
            'logo_raw' => ['nullable', new IsBase64Image(['size' => config('upload.images.max_size')])]
        ];
    }
}
