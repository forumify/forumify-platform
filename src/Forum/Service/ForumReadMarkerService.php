<?php

declare(strict_types=1);

namespace Forumify\Forum\Service;

use Forumify\Core\Entity\User;
use Forumify\Core\Repository\ReadMarkerRepository;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Core\Service\ReadMarkerServiceInterface;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\Topic;
use Symfony\Bundle\SecurityBundle\Security;

class ForumReadMarkerService implements ReadMarkerServiceInterface
{
    public function __construct(
        private readonly ReadMarkerRepository $readMarkerRepository,
        private readonly Security $security,
    ) {
    }

    public function supports(mixed $subject): bool
    {
        return $subject instanceof Forum;
    }

    /**
     * @param Forum $subject
     */
    public function read(User $user, mixed $subject): bool
    {
        $canViewHidden = $this->security->isGranted(VoterAttribute::Moderator->value);
        $topicIds = $this->getTopicIds($user, $subject, $canViewHidden);
        return $this->readMarkerRepository->areAllRead($user, Topic::class, $topicIds);
    }

    public function markAsRead(User $user, mixed $subject): void
    {
        $canViewHidden = $this->security->isGranted(VoterAttribute::Moderator->value);
        $topicIds = $this->getTopicIds($user, $subject, $canViewHidden);
        $this->readMarkerRepository->markAllRead($user, Topic::class, $topicIds);
    }

    /**
     * @return array<int>
     */
    private function getTopicIds(User $user, Forum $forum, bool $canViewHidden): array
    {
        $onlyShowOwnTopics = $forum->getDisplaySettings()->isOnlyShowOwnTopics();
        $canViewAll = !$onlyShowOwnTopics || $this->security->isGranted(VoterAttribute::ACL->value, ['entity' => $forum, 'permission' => 'show_all_topics']);

        $visibleTopics = $canViewAll
            ? $forum->getTopics()
            : $forum->getTopics()->filter(fn (Topic $topic) => $topic->getCreatedBy()?->getId() === $user->getId());

        $ownTopicIds = $visibleTopics
            ->filter(fn (Topic $topic) => !$topic->isHidden() || $canViewHidden)
            ->map(fn (Topic $topic) => $topic->getId())
            ->toArray();

        $childTopicIds = $forum->getChildren()
            ->filter(fn (Forum $subForum) => $this->security->isGranted(VoterAttribute::ACL->value, [
                'permission' => 'view',
                'entity' => $subForum,
            ]))
            ->map(fn (Forum $subForum) => $this->getTopicIds($user, $subForum, $canViewHidden))
            ->toArray();

        return array_merge($ownTopicIds, ...$childTopicIds);
    }
}
