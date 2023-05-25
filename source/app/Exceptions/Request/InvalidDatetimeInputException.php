<?php

namespace App\Exceptions\Request;

use App\Exceptions\CodedException;
use App\Helpers\Enums\ErrorCodes;
use Throwable;

class InvalidDatetimeInputException extends CodedException
{
    public function __construct(int $code = ErrorCodes::ERR_INVALID_DATETIME_INPUT_DATA, string $message = null, ?Throwable $previous = null)
    {
        parent::__construct($code, $message, $previous);
    }
}
