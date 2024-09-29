<?php

declare(strict_types=1);

namespace Forumify\Forum\Service;

use Forumify\Core\Entity\User;
use Forumify\Core\Repository\ReadMarkerRepository;
use Forumify\Core\Service\ReadMarkerServiceInterface;
use Forumify\Forum\Entity\MessageThread;

class MessageThreadReadMarkerService implements ReadMarkerServiceInterface
{
    public function __construct(private readonly ReadMarkerRepository $readMarkerRepository)
    {
    }

    public function supports(mixed $subject): bool
    {
        return $subject instanceof MessageThread;
    }

    /**
     * @param MessageThread $subject
     */
    public function read(User $user, mixed $subject): bool
    {
        return $this->readMarkerRepository->isRead($user, MessageThread::class, $subject->getId());
    }

    public function markAsRead(User $user, mixed $subject): void
    {
    }
}
