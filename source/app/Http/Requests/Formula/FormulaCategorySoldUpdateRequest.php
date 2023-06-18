<?php

namespace App\Http\Requests\Formula;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FormulaCategorySoldUpdateRequest extends BaseRequest
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
        $formula_id = $this->input('formula_id');
        $category_sold_id = $this->input('category_sold_id');
        return [
            'formula_id' => [
                'required',
                'integer',
                'exists:formulas,id',
                Rule::unique('formula_category_solds')->where(function ($query) use ($formula_id, $category_sold_id) {
                    return $query->where([
                        ['formula_id', $formula_id],
                        ['category_sold_id', $category_sold_id]
                    ]);
                })->ignore($id)
            ],
            'category_sold_id' => ['required', 'integer', 'exists:category_solds,id'],
            'value_from' => ['required', 'numeric'],
            'value_to' => ['required', 'numeric', 'required_with:value_from', 'gte:value_from'],
        ];
    }
}
