<?php

declare(strict_types=1);

namespace Forumify\Core\Exception;

use Exception;

class UserAlreadyExistsException extends Exception
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
