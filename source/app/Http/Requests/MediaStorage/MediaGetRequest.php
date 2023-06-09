<?php

namespace App\Http\Requests\MediaStorage;

use App\Helpers\Responses\HttpStatuses;
use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class MediaGetRequest extends BaseRequest
{
    /**
     * Overwrite this function to prepare or convert data before validating
     * @return void
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
        $inputs = $this->input();
        $disk = Route::input('disk');
        if ($disk == 'tmp') abort(HttpStatuses::HTTP_NOT_FOUND); // Don't accept if the `tmp` is specified, to protect the server
        $inputs = array_merge($inputs, ['disk' => $disk ?? 'tmp']);
        $inputs = array_merge($inputs, ['id' => Route::input('id')]);
        $this->replace($inputs);
    }

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
            'url' => ['required', 'string']
        ];
    }
}
