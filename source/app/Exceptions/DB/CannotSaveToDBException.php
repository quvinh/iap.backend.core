<?php
namespace App\Exceptions\DB;

use App\Exceptions\CodedException;
use App\Helpers\Enums\ErrorCodes;
use Exception;
use Throwable;

class CannotSaveToDBException extends CodedException {
    public function __construct(int $code = ErrorCodes::ERR_CANNOT_SAVE_TO_DB, string $message = null, ?Throwable $previous = null)
    {
        parent::__construct($code, $message, $previous);
    }
}
