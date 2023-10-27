<?php

namespace App\Http\Requests\InvoiceMedia;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class InvoiceMediaReadRequest extends BaseRequest
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
            'id' => ['required', 'numeric'],
            // 'key' => ['required', 'string'],
            'format' => ['string'],
            'idx_item' => ['required', 'numeric'],
            'idx_unit' => ['required', 'numeric'],
            'idx_amount' => ['required', 'numeric'],
            'idx_price' => ['required', 'numeric'],
            'idx_total' => ['required', 'numeric'],
        ];
    }
}
