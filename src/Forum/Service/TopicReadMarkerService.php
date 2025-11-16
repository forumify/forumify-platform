<?php

declare(strict_types=1);

namespace Forumify\Forum\Service;

use Forumify\Core\Entity\User;
use Forumify\Core\Repository\ReadMarkerRepository;
use Forumify\Core\Service\ReadMarkerServiceInterface;
use Forumify\Forum\Entity\Topic;

/**
 * @implements ReadMarkerServiceInterface<Topic>
 */
class TopicReadMarkerService implements ReadMarkerServiceInterface
{
    public function __construct(private readonly ReadMarkerRepository $readMarkerRepository)
    {
    }

    public static function getEntityClass(): string
    {
        return Topic::class;
    }

    public function read(User $user, mixed $subject): bool
    {
        return $this->readMarkerRepository->isRead($user, Topic::class, $subject->getId());
    }

    public function markAsRead(User $user, mixed $subject): void
    {
        $this->readMarkerRepository->read($user, Topic::class, $subject->getId());
    }
}
