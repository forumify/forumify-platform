<?php

declare(strict_types=1);

namespace Forumify\Forum\Twig\Extension;

use Forumify\Core\Entity\User;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\Topic;
use Forumify\Forum\Service\LastCommentService;
use Forumify\Forum\Service\UserReputationService;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\RuntimeExtensionInterface;

class ForumExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly LastCommentService $lastCommentService,
        private readonly UserReputationService $userReputationService,
    ) {
    }

    public function getLastComment(Forum|Topic|null $subject): ?Comment
    {
        if ($subject === null) {
            return null;
        }

        if ($subject instanceof Forum && !$subject->getDisplaySettings()->isShowLastCommentBy()) {
            return null;
        }

        if ($subject instanceof Topic && !$subject->getForum()->getDisplaySettings()->isShowTopicLastCommentBy()) {
            return null;
        }

        return $this->lastCommentService->getLastComment($subject);
    }

    public function canMarkAsAnswer(?Comment $comment): bool
    {
        if ($comment === null) {
            return false;
        }

        $topic = $comment->getTopic();
        if ($topic->getForum()->getType() !== Forum::TYPE_SUPPORT) {
            // only available in support forums
            return false;
        }

        if ($comment->getId() === $topic->getFirstComment()?->getId()) {
            // The first comment should always be the question
            return false;
        }

        if ($comment->getId() === $topic->getAnswer()?->getId()) {
            // already marked as the answer
            return false;
        }

        return $this->security->isGranted(VoterAttribute::CommentMarkAsAnswer->value, $comment);
    }

    public function getUserReputation(?User $user): int
    {
        if ($user === null) {
            return 0;
        }

        return $this->userReputationService->getReputation($user);
    }
}
