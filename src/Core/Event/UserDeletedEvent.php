<?php

declare(strict_types=1);

namespace Forumify\Core\Event;

use Forumify\Core\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Dispatched before the user is removed, so listeners can still act on content they created.
 */
class UserDeletedEvent extends Event
{
    public function __construct(
        public readonly User $user,
        public readonly bool $deleteContent = false,
    ) {
    }
}
