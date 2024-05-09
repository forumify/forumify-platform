<?php

declare(strict_types=1);

namespace Forumify\Plugin\Application\Exception;

class PluginNotFoundException extends PluginException
{
    public function __construct(
        int $id,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct("Unable to find plugin with ID: $id.", $code, $previous);
    }
}
