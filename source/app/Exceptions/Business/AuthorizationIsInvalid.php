<?php

namespace App\Exceptions\Business;

use App\Exceptions\CodedException;
use App\Helpers\Enums\ErrorCodes;
use Throwable;

class AuthorizationIsInvalid extends CodedException
{
    public function __construct(int $code = ErrorCodes::ERR_INVALID_AUTHORIZATION, string $message = null, ?Throwable $previous = null)
    {
        parent::__construct($code, $message, $previous);
    }
}
