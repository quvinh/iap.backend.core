<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends BaseRequest
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
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:100', 'unique:users,username,' . $this->id],
            'email' => ['required', 'string', 'email', 'unique:users,email,' . $this->id],
            'phone' => ['nullable', 'string', 'min:10', 'max:20'],
            'photo' => ['nullable', 'string'],
            'birthday' => ['nullable', 'date_format:Y-m-d'],
            'address' => ['nullable', 'string'],
        ];
    }
}
