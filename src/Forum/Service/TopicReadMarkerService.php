<?php

declare(strict_types=1);

namespace Forumify\Forum\Service;

use Forumify\Core\Entity\User;
use Forumify\Core\Repository\ReadMarkerRepository;
use Forumify\Core\Service\ReadMarkerServiceInterface;
use Forumify\Forum\Entity\Topic;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @implements ReadMarkerServiceInterface<Topic>
 */
class TopicReadMarkerService implements ReadMarkerServiceInterface, ResetInterface
{
    /** @var array<int, array<int, bool>> read state per topic id, per user id */
    private array $readTopics = [];

    public function __construct(private readonly ReadMarkerRepository $readMarkerRepository)
    {
    }

    public static function getType(): string
    {
        return 'topic';
    }

    public static function getEntityClass(): string
    {
        return Topic::class;
    }

    public function read(User $user, mixed $subject): bool
    {
        return $this->readTopics[$user->getId()][$subject->getId()]
            ?? $this->readMarkerRepository->isRead($user, Topic::class, $subject->getId());
    }

    public function markAsRead(User $user, mixed $subject): void
    {
        $this->readMarkerRepository->read($user, Topic::class, $subject->getId());
        $this->reset();
    }

    /**
     * @param array<Topic> $subjects
     */
    public function preload(User $user, array $subjects): void
    {
        $topicIds = array_map(static fn (Topic $topic) => $topic->getId(), $subjects);
        $read = array_flip($this->readMarkerRepository->findReadSubjectIds($user, Topic::class, $topicIds));

        foreach ($topicIds as $topicId) {
            $this->readTopics[$user->getId()][$topicId] = isset($read[$topicId]);
        }
    }

    public function reset(): void
    {
        $this->readTopics = [];
    }
}
