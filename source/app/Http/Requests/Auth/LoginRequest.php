<?php

namespace App\Http\Requests\Auth;


use App\Http\Requests\BaseRequest;
use App\Rules\IsPhoneNumber;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\RequiredIf;

class LoginRequest extends BaseRequest
{
    /**
     * Overwrite this function to prepare or convert data before validating
     * @return void
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
    }

    /**
     * @return string[]
     */
    public function attributes(): array
    {
        return [];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string'],
            'password' => ['required', 'min:6'],
        ];
    }
}
