<?php

namespace App\Exceptions\DB;

use App\Exceptions\CodedException;
use App\Helpers\Enums\ErrorCodes;
use Throwable;

class IdIsNotProvidedException extends CodedException
{
    public function __construct(int $code = ErrorCodes::ERR_ID_IS_NOT_PROVIDED, string $message = null, ?Throwable $previous = null)
    {
        parent::__construct($code, $message, $previous);
    }
}
