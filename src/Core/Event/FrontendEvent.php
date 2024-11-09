<?php

declare(strict_types=1);

namespace Forumify\Core\Event;

use Symfony\Contracts\EventDispatcher\Event;

abstract class FrontendEvent extends Event
{
    public function __construct(
        public readonly string $frontend
    ) {
    }
}
