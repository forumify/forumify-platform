<?php

declare(strict_types=1);

namespace Forumify\Api\Exception;

use Throwable;

class InvalidGrantException extends GenericOAuthException
{
    public function __construct(?string $errorDescription = null, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('invalid_grant', $errorDescription, $code, $previous);
    }
}
