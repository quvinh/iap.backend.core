<?php

namespace App\Http\Requests\InvoiceDetail;

use App\Http\Requests\DefaultSearchRequest;
use Illuminate\Foundation\Http\FormRequest;

class InvoiceDetailSearchRequest extends DefaultSearchRequest
{
    /**
     * Available relations to retrieve
     * @var string[]
     */
    protected array $relations = [
        'invoice',
        'item_code',
    ];

    protected array $fields = [
        'id',
        'invoice_id',
        'formula_id',
        'formula_path_id',
        'formula_commodity_id',
        'formula_material_id',
        'item_code_id',
        'formula_group_name',
        'product',
        'product_exchange',
        'unit',
        'quantity',
        'price',
        'vat',
        'vat_money',
        'total_money',
        'warehouse',
        'main_entity',
        'visible',
        'note',
        'icp_price',
        'isf_price',
        'import_tax',
        'special_consumption_tax',
        'customs_code',
        'withs',

        'type',
        'start_date',
        'end_date',
        'company_id',
        'product_name',
        'price_from',
        'price_to',
        'product_code',
        'product_name_from_item_code',
        'unit_from_item_code',
    ];

    /**
     * Overwrite this function to prepare or convert data before validating
     * @return void
     * @throws InvalidDatetimeInputException
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return parent::authorize();
    }

    /**
     * @return string[]
     */
    public function attributes(): array
    {
        return parent::attributes();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return parent::rules();
    }
}
