<?php
namespace App\Exceptions\DB;

use App\Exceptions\CodedException;
use App\Helpers\Enums\ErrorCodes;
use Throwable;

class CannotUpdateDBException extends CodedException{

    public function __construct(int $code = ErrorCodes::ERR_CANNOT_UPDATE_RECORD, string $message = null, ?Throwable $previous = null)
    {
        parent::__construct($code, $message, $previous);
    }

}
