<?php

declare(strict_types=1);

namespace Forumify\Core\Twig\Extension;

use DateTime;
use Forumify\Core\Entity\User;
use Twig\Extension\RuntimeExtensionInterface;

class OnlineExtensionRuntime implements RuntimeExtensionInterface
{
    /**
     * @return bool true if the user did anything in the past 5 minutes
     */
    public function isOnline(User $user): bool
    {
        $lastActivity = $user->getLastActivity();
        if ($lastActivity === null) {
            return false;
        }

        $now = new DateTime();
        $diff = $now->getTimestamp() - $lastActivity->getTimestamp();
        return $diff < 300;
    }
}

