<?php

namespace App\Exceptions;

use App\Helpers\Enums\ErrorCodes;
use Exception;
use Throwable;

class CodedException extends \Exception
{
    /** @var mixed $code */
    protected $code;

    /**
     * @param mixed $code
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct(mixed $code = 0, string $message = null, ?Throwable $previous = null)
    {
        $this->code = $code;
        parent::__construct(message: $message, code: $code, previous: $previous);
        if (is_null($message)) {
            try {
                $this->message = __(ErrorCodes::getKey($code));
            } catch (Exception $ex) {
                // pass
            }
        }
    }
}
