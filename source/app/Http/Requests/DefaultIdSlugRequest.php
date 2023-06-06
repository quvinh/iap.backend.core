<?php

namespace App\Http\Requests;


use Illuminate\Support\Facades\Route;

class DefaultIdSlugRequest extends BaseRequest
{
    /**
     * Overwrite this function to prepare or convert data before validating
     * @return void
     */
    protected function prepareForValidation(): void {
        parent::prepareForValidation();
        $inputs = $this->only(['id']);
        $inputs = array_merge($inputs, [
            'withs' => $this->get('withs')?? [],
            'meta' => $this->get('meta')?? []
        ]);
        // withs
        if (isset($inputs['withs'])) {
            $withs = $inputs['withs'];
            if (is_string($withs))
                $withs = json_decode($withs, true);
            $inputs = array_merge($inputs, ['withs' => $withs]);
        }
        $inputs = array_merge($inputs, ['id' => Route::input('id')]);
        $this->replace($inputs);
    }

    /**
    * @return string[]
    */
    public function attributes(): array
    {
        return [
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer']
        ];
    }
}
