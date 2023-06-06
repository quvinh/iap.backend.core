<?php

namespace App\Http\Requests;

use App\Exceptions\Request\InvalidDatetimeInputException;
use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class DefaultSearchRequest extends BaseRequest
{
    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->fields = array_merge($this->fields, [
            'name',
            'pagination',
            'sort',
            'created_date',
            'updated_date'
        ]);
    }

    /**
     * Overwrite this function to prepare or convert data before validating
     * @return void
     * @throws InvalidDatetimeInputException
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
        $inputs = $this->only($this->fields);
        // withs
        if (isset($inputs['withs'])) {
            $withs = $this['withs'];
            if (is_string($withs))
                $withs = json_decode($withs, true);
            $inputs = array_merge($inputs, ['withs' => $withs]);
        }
        // sort
        if (isset($inputs['sort'])) {
            $range = $this['sort'];
            if (is_string($range))
                $range = json_decode($range, true);
            $inputs = array_merge($inputs, ['sort' => $range]);
        }
        // date ranges
        if (isset($inputs['created_date'])) {
            $range = $this['created_date'];
            if (is_string($range)) {
                $range = json_decode($range, true);
                if (is_null($range))
                    throw new InvalidDatetimeInputException('ERR_TIMESTAMP');
            }
            $inputs = array_merge($inputs, ['created_date' => $range]);
        }
        if (isset($inputs['updated_date'])) {
            $range = $this['updated_date'];
            if (is_string($range)) {
                $range = json_decode($range, true);
                if (is_null($range))
                    throw new InvalidDatetimeInputException('ERR_TIMESTAMP');
            }
            $inputs = array_merge($inputs, ['updated_date' => $range]);
        }
        $this->replace($inputs);
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        //TODO: Fix later with authentication
        return true;
        //return auth()->check();
    }

    /**
     * @return string[]
     */
    public function attributes(): array
    {
        return [
            'pagination.page' => 'page number',
            'pagination.per_page' => 'page size number',
            'created_date.from' => 'created date (from)',
            'created_date.to' => 'created date (to)',
            'updated_date.from' => 'updated date (from)',
            'updated_date.to' => 'updated date (to)',
            'type' => 'approach type',
            'sort.column' => 'sort column',
            'sort.type' => 'sort type'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<mixed>
     */
    public function rules(): array
    {
        $listOfColumn = ['id', 'status', 'type', 'trigger_day', 'created_at', 'updated_at'];
        return [
            // 'name' => ['nullable', 'string'],
            // 'created_date' => ['nullable', 'min:1'],
            // 'created_date.from' => ['date_format:Y-m-d'],
            // 'created_date.to' => ['date_format:Y-m-d'],
            // 'updated_date' => ['nullable'],
            // 'updated_date.from' => ['date_format:Y-m-d'],
            // 'updated_date.to' => ['date_format:Y-m-d']
        ];
    }
}
