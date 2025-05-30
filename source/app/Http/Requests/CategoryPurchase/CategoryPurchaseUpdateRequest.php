<?php

namespace App\Http\Requests\CategoryPurchase;

use App\Helpers\Enums\CategoryActions;
use App\Helpers\Enums\CategoryTags;
use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryPurchaseUpdateRequest extends BaseRequest
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
            'name' => ['required', 'string', 'unique:category_purchases,name,' . $this->id],
            'tag' => ['nullable', 'string', Rule::in(CategoryTags::getValues())],
            'method' => ['nullable', 'string', Rule::in(CategoryActions::getValues())],
        ];
    }
}
