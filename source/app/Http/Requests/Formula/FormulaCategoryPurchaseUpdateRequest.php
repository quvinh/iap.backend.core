<?php

namespace App\Http\Requests\Formula;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FormulaCategoryPurchaseUpdateRequest extends BaseRequest
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
        $category_purchase_id = $this->input('category_purchase_id');
        return [
            'formula_id' => [
                'required',
                'integer',
                'exists:formulas,id',
                Rule::unique('formula_category_purchases')->where(function ($query) use ($formula_id, $category_purchase_id) {
                    return $query->where([
                        ['formula_id', $formula_id],
                        ['category_purchase_id', $category_purchase_id]
                    ]);
                })->ignore($id)
            ],
            'category_purchase_id' => ['required', 'integer', 'exists:category_purchases,id'],
            'value_from' => ['required', 'numeric'],
            'value_to' => ['required', 'numeric', 'required_with:value_from', 'gte:value_from'],
        ];
    }
}
