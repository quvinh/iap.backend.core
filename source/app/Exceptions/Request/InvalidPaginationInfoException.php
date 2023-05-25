<?php

namespace App\Exceptions\Request;

use App\Exceptions\CodedException;
use App\Helpers\Enums\ErrorCodes;
use Throwable;

class InvalidPaginationInfoException extends CodedException
{
    public function __construct(int $code = ErrorCodes::ERR_PAGINATION_INPUT_DATA, string $message = null, ?Throwable $previous = null)
    {
        parent::__construct($code, $message, $previous);
    }
}
