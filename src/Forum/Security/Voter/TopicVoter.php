<?php

declare(strict_types=1);

namespace Forumify\Forum\Security\Voter;

use Forumify\Core\Entity\User;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Core\Service\ACLService;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\Topic;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Topic|Forum>
 */
class TopicVoter extends Voter
{
    public function __construct(private readonly ACLService $aclService)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
            VoterAttribute::TopicView->value,
            VoterAttribute::TopicCreate->value,
            VoterAttribute::TopicEdit->value,
            VoterAttribute::TopicDelete->value,
        ], true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $forum = $subject instanceof Topic ? $subject->getForum() : $subject;
        if ($this->aclService->can('moderate', $forum)) {
            return true;
        }

        /** @var User|null $user */
        $user = $token->getUser();
        return match ($attribute) {
            VoterAttribute::TopicView->value => $subject instanceof Topic && $this->voteOnView($user, $subject),
            VoterAttribute::TopicCreate->value => $subject instanceof Forum && $this->voteOnCreate($user, $subject),
            VoterAttribute::TopicEdit->value,
            VoterAttribute::TopicDelete->value => $subject instanceof Topic && $this->voteOnEditOrDelete($user, $subject),
            default => false,
        };
    }

    private function voteOnView(?User $user, Topic $topic): bool
    {
        if ($topic->isHidden()) {
            return false;
        }

        $forum = $topic->getForum();
        if (!$this->aclService->can('view', $forum)) {
            return false;
        }

        $onlyShowOwnTopics = $forum->getDisplaySettings()->isOnlyShowOwnTopics();
        $author = $topic->getCreatedBy();
        $isOwnTopic = $author !== null && $author->getId() === $user?->getId();
        if (!$onlyShowOwnTopics || $isOwnTopic) {
            return true;
        }

        return $this->aclService->can('show_all_topics', $forum);
    }

    private function voteOnCreate(?User $user, Forum $forum): bool
    {
        if ($user === null || !$user->isEmailVerified() || $user->isBanned()) {
            return false;
        }

        return $this->aclService->can('create_topic', $forum);
    }

    private function voteOnEditOrDelete(?User $user, Topic $topic): bool
    {
        if ($user === null || $user->isBanned()) {
            return false;
        }

        if ($topic->getCreatedBy()?->getId() === $user->getId()) {
            return true;
        }

        return false;
    }
}
