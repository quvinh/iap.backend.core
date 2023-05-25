<?php

namespace App\Exceptions\Business;

use App\Exceptions\CodedException;
use App\Helpers\Enums\ErrorCodes;
use Throwable;

class NoPermissionException extends CodedException
{
    public function __construct(int $code = ErrorCodes::ERR_NO_PERMISSION, string $message = null, ?Throwable $previous = null)
    {
        parent::__construct($code, $message, $previous);
    }
}
