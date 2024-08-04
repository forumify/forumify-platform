<?php
declare(strict_types=1);

namespace Forumify\Api\Exception;

use Throwable;

/**
 * RFC6749 4.1.2.1/5.2 Error Response
 */
interface OAuthExceptionInterface extends Throwable
{
    public function getError(): string;

    public function getErrorDescription(): ?string;
}
