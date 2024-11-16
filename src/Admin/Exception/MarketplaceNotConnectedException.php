<?php

declare(strict_types=1);

namespace Forumify\Admin\Exception;

use Exception;

class MarketplaceNotConnectedException extends Exception
{
    public function __construct(
        string $message = 'Marketplace is not connected.',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
