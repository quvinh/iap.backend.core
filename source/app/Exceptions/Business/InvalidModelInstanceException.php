<?php

namespace App\Exceptions\Business;

use App\Exceptions\CodedException;
use App\Helpers\Enums\ErrorCodes;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;
use Throwable;

class InvalidModelInstanceException extends CodedException
{
    /**
     * @throws InvalidEnumMemberException
     */
    public function __construct(int $code = ErrorCodes::ERR_MODEL_CLASS_NOT_EXISTS, string $message = null, ?Throwable $previous = null)
    {
        parent::__construct($code, $message, $previous);
    }
}
