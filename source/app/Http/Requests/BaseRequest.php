<?php

namespace App\Http\Requests;

use App\DataResources\PaginationInfo;
use App\Exceptions\Request\InvalidPaginationInfoException;
use Illuminate\Foundation\Http\FormRequest;

class BaseRequest extends FormRequest
{
    protected array $fields = [
        'meta', 'withs'
    ];

    protected array $relations = [];
    /**
     * Overwrite this function to prepare or convert data before validating
     * @return void
     */
    protected function prepareForValidation()
    {
        if ($this->isJson() && $this->json) {
            $this->replace($this->json->all());
        }

        $inputs = $this->input();
        $pagingParam = $this->parsePaginationParam();
        $withs = array_filter($inputs['withs'] ?? [], function ($value) {
            return in_array($value, $this->relations);
        });
        $inputs['withs'] = $withs;
        $inputs = array_merge($inputs, $pagingParam);
        $this->replace($inputs);
    }

    /**
     * Correct raw pagination parameter to support
     * 1. URL query: using json string (disadvantage: ugly url)
     * 2. Send json object in request body (disadvantage: not supported by some webserver)
     * @return array<string, mixed>
     */
    private function parsePaginationParam(): array
    {
        if ($this->has('pagination')) {
            $paging = $this['pagination'];
            if (is_string($this['pagination']))
                $paging = json_decode($this['pagination'], true);

            return ['pagination' => $paging];
        }
        return [];
    }


    /**
     * Return PaginationInfo
     * @throws InvalidPaginationInfoException
     */
    public function getPaginationInfo(): ?PaginationInfo
    {
        return $this->has('pagination') ? PaginationInfo::parse($this['pagination']) : null;
    }

    /**
     * be override
     * @return array
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Default validate
     */
    public function validate(): array
    {
        $this->prepareForValidation();
        return parent::validate($this->rules(), $this->messages(), $this->attributes());
    }

    public function addField(string $field)
    {
        $this->fields = array_merge($this->fields, [$field]);
    }
}
