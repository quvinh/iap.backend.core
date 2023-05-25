<?php

namespace App\Exceptions\DB;

use App\Exceptions\CodedException;
use App\Helpers\Enums\ErrorCodes;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;
use Exception;
use Throwable;

class RecordIsNotFoundException extends CodedException
{
    /**
     * @param mixed $code
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct(int $code = ErrorCodes::ERR_RECORD_NOT_FOUND, string $message = null, ?Throwable $previous = null)
    {
        parent::__construct($code, $message, $previous);
    }
}
