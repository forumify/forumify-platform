<?php

declare(strict_types=1);

namespace Forumify\Forum\Service;

use Forumify\Core\Entity\User;
use Forumify\Core\Repository\ReadMarkerRepository;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Core\Service\ReadMarkerCheckerInterface;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\Topic;
use Symfony\Bundle\SecurityBundle\Security;

class ForumReadMarkerChecker implements ReadMarkerCheckerInterface
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
        $topicIds = $this->getTopicIds($subject);
        return $this->readMarkerRepository->areAllRead($user, Topic::class, $topicIds);
    }

    private function getTopicIds(Forum $forum): array
    {
        $ownTopicIds = $forum->getTopics()
            ->filter(fn (Topic $topic) => !$topic->isHidden() || $this->security->isGranted(VoterAttribute::Moderator->value))
            ->map(fn (Topic $topic) => $topic->getId())
            ->toArray();

        $childTopicIds = $forum->getChildren()
            ->filter(fn (Forum $subForum) => $this->security->isGranted(VoterAttribute::ACL->value, [
                'permission' => 'view',
                'entity' => $subForum,
            ]))
            ->map(fn (Forum $subForum) => $this->getTopicIds($subForum))
            ->toArray();

        return array_merge($ownTopicIds, ...$childTopicIds);
    }
}
