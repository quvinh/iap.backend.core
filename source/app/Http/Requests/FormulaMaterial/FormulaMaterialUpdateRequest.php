<?php

namespace App\Http\Requests\FormulaMaterial;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FormulaMaterialUpdateRequest extends BaseRequest
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
        $id = $this->id;
        $name = $this->input('name');
        $formula_id = $this->input('formula_id');
        return [
            'name' => [
                'required',
                'string',
                Rule::unique('formula_materials')->where(function ($query) use ($name, $formula_id) {
                    return $query->where([
                        ['name', $name],
                        ['formula_id', $formula_id]
                    ]);
                })->ignore($id)
            ],
            'formula_id' => ['required', 'integer', 'exists:formulas,id'],
            'value_from' => ['required', 'numeric'],
            'value_to' => ['required', 'numeric', 'required_with:value_from', 'gte:value_from'],
        ];
    }
}
