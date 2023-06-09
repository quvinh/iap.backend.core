<?php

namespace App\Rules;

use App\Helpers\Enums\SupportedImageTypes;
use finfo;
use Illuminate\Contracts\Validation\Rule;
use PHPUnit\Util\Exception;

class IsBase64Image implements Rule
{
    private $attribute;
    private int $size;
    private bool $isInvalidSize = false;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($params)
    {
        $maxsize = config('upload.images.max_size') *  1048576;
        try {
            $this->size = intval($params['size']) *  1048576;
        } catch (\Exception $ex) {
            $this->size = $maxsize;
        }
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        try {
            $this->attribute = $attribute;
            $image = base64_decode($value);
            $size = strlen($image);
            if ($size > $this->size) throw new Exception('Invalid size');
            $image_info = getimagesize($value);
            $mime = $image_info['mime'] ?? '';
            return in_array($mime, SupportedImageTypes::getValues());
        } catch (\Exception $ex) {
            $this->isInvalidSize = true;
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if ($this->isInvalidSize) return 'The field ' . $this->attribute . ' has an invalid size information';
        return 'The field ' . $this->attribute . ' is not an image data.';
    }
}
