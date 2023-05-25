<?php
namespace App\Exceptions\DB;

use App\Exceptions\CodedException;
use App\Helpers\Enums\ErrorCodes;
use Exception;
use Throwable;

class CannotDeleteDBException extends CodedException {

    public function __construct(int $code = ErrorCodes::ERR_CANNOT_DELETE_RECORD, string $message = null, ?Throwable $previous = null)
    {
        parent::__construct($code, $message, $previous);
    }

}
