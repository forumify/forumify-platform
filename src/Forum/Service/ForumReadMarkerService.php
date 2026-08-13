<?php

declare(strict_types=1);

namespace Forumify\Forum\Service;

use Forumify\Core\Entity\User;
use Forumify\Core\Repository\ReadMarkerRepository;
use Forumify\Core\Service\ReadMarkerServiceInterface;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\Topic;
use Forumify\Forum\Repository\TopicRepository;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @implements ReadMarkerServiceInterface<Forum>
 */
class ForumReadMarkerService implements ReadMarkerServiceInterface, ResetInterface
{
    /** @var array<int, array<int, true>> unread forum ids, per user id */
    private array $unreadForums = [];

    public function __construct(
        private readonly ReadMarkerRepository $readMarkerRepository,
        private readonly TopicRepository $topicRepository,
        private readonly ForumTreeService $forumTree,
        private readonly ForumVisibilityService $forumVisibility,
    ) {
    }

    public static function getEntityClass(): string
    {
        return Forum::class;
    }

    public function read(User $user, mixed $subject): bool
    {
        return !isset($this->getUnreadForums($user)[$subject->getId()]);
    }

    public function markAsRead(User $user, mixed $subject): void
    {
        $visibility = $this->forumVisibility->getTopicVisibilityInTree($subject);
        $topicIds = $this->topicRepository->findVisibleTopicIds($user, $visibility);
        $this->readMarkerRepository->markAllRead($user, Topic::class, $topicIds);

        $this->reset();
    }

    public function reset(): void
    {
        $this->unreadForums = [];
    }

    /**
     * A forum is unread when it, or any of the sub forums the user can view, contains a topic without
     * a read marker. Resolving that per forum means walking the same tree over and over, so it is
     * determined for all forums at once in a single query.
     *
     * @return array<int, true> forum ids, used as a set
     */
    private function getUnreadForums(User $user): array
    {
        $userId = $user->getId();
        if (isset($this->unreadForums[$userId])) {
            return $this->unreadForums[$userId];
        }

        $forums = $this->forumTree->getForums();
        $visibility = $this->forumVisibility->getTopicVisibilityPerForum();

        $unread = [];
        foreach ($this->topicRepository->findForumIdsWithUnreadTopics($user, $visibility) as $forumId) {
            for ($forum = $forums[$forumId] ?? null; $forum !== null; $forum = $forum->getParent()) {
                $id = $forum->getId();
                if (isset($unread[$id]) || !isset($visibility[$id])) {
                    break;
                }

                $unread[$id] = true;
            }
        }

        return $this->unreadForums[$userId] = $unread;
    }
}
