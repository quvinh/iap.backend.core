<?php

namespace App\Http\Requests\FirstAriseAccount;

use App\Helpers\Enums\AriseAccountTypes;
use App\Http\Requests\BaseRequest;
use App\Rules\IsBase64Image;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FirstAriseAccountUpdateRequest extends BaseRequest
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
            'name' => ['required', 'string', 'unique:first_arise_accounts,name,' . $this->id],
            'number_account' => ['nullable', 'string'],
            'is_tracking' => ['numeric', Rule::in(AriseAccountTypes::getValues())],
        ];
    }
}
