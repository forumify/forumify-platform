<?php

declare(strict_types=1);

namespace Forumify\Plugin\Application\Exception;

class UnbootableKernelException extends PluginException
{
    public function __construct(
        string $message = 'The kernel can not be booted.',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
