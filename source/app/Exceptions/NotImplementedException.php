<?php
namespace App\Exceptions;

use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use Throwable;

class NotImplementedException extends \Exception {
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        $message = ! empty($message)? $message: strval(__("This method is not implemented yet"));
        parent::__construct($message, $code, $previous);
    }
}
