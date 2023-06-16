<?php

namespace App\Http\Requests\CompanyDetail;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AriseAccountUpdateRequest extends BaseRequest
{
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
            'value_from' => ['required', 'numeric'],
            'value_to' => ['required', 'numeric', 'required_with:value_from', 'gte:value_from'],
        ];
    }
}
