<?php

namespace App\Http\Requests\FormulaCommodity;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FormulaCommodityCreateRequest extends BaseRequest
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
        $name = $this->input('name');
        $formula_id = $this->input('formula_id');
        return [
            'name' => [
                'required',
                'string',
                Rule::unique('formula_commodities')->where(function ($query) use ($name, $formula_id) {
                    return $query->where([
                        ['name', $name],
                        ['formula_id', $formula_id]
                    ]);
                })
            ],
            'formula_id' => ['required', 'integer', 'exists:formulas,id']
        ];
    }
}
