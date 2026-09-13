<?php

declare(strict_types=1);

namespace Forumify\Forum\Service;

use Forumify\Core\Entity\User;
use Forumify\Core\Repository\ReadMarkerRepository;
use Forumify\Core\Service\ReadMarkerServiceInterface;
use Forumify\Forum\Entity\MessageThread;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @implements ReadMarkerServiceInterface<MessageThread>
 */
class MessageThreadReadMarkerService implements ReadMarkerServiceInterface, ResetInterface
{
    /** @var array<int, array<int, bool>> read state per thread id, per user id */
    private array $readThreads = [];

    public function __construct(private readonly ReadMarkerRepository $readMarkerRepository)
    {
    }

    public static function getType(): string
    {
        return 'message_thread';
    }

    public static function getEntityClass(): string
    {
        return MessageThread::class;
    }

    public function read(User $user, mixed $subject): bool
    {
        return $this->readThreads[$user->getId()][$subject->getId()]
            ?? $this->readMarkerRepository->isRead($user, MessageThread::class, $subject->getId());
    }

    public function markAsRead(User $user, mixed $subject): void
    {
        $this->readMarkerRepository->read($user, MessageThread::class, $subject->getId());
        $this->reset();
    }

    /**
     * @param array<MessageThread> $subjects
     */
    public function preload(User $user, array $subjects): void
    {
        $threadIds = array_map(static fn (MessageThread $thread) => $thread->getId(), $subjects);
        $read = array_flip($this->readMarkerRepository->findReadSubjectIds($user, MessageThread::class, $threadIds));

        foreach ($threadIds as $threadId) {
            $this->readThreads[$user->getId()][$threadId] = isset($read[$threadId]);
        }
    }

    public function reset(): void
    {
        $this->readThreads = [];
    }
}
