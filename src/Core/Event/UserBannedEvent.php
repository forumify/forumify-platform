<?php

declare(strict_types=1);

namespace Forumify\Core\Event;

use Forumify\Core\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserBannedEvent extends Event
{
    public function __construct(
        public readonly User $user,
        public readonly bool $deleteContent = false,
    ) {
    }
}
