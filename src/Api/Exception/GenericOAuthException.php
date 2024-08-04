<?php

declare(strict_types=1);

namespace Forumify\Api\Exception;

use Exception;
use Throwable;

class GenericOAuthException extends Exception implements OAuthExceptionInterface
{
    public function __construct(
        private readonly string $error = '',
        private readonly ?string $errorDescription = null,
        int $code = 0,
        ?Throwable $previous = null
    )
    {
        parent::__construct($error, $code, $previous);
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function getErrorDescription(): ?string
    {
        return $this->errorDescription;
    }
}
