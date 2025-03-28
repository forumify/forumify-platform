<?php

declare(strict_types=1);

namespace Forumify\Forum\Security\Voter;

use Forumify\Core\Entity\User;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\Topic;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Topic|Forum>
 */
class TopicVoter extends Voter
{
    public function __construct(private readonly Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
            VoterAttribute::TopicView->value,
            VoterAttribute::TopicCreate->value,
            VoterAttribute::TopicEdit->value,
            VoterAttribute::TopicDelete->value
        ], true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if ($this->security->isGranted(VoterAttribute::Moderator->value, $subject)) {
            return true;
        }

        /** @var User|null $user */
        $user = $token->getUser();
        return match ($attribute) {
            VoterAttribute::TopicView->value => $this->voteOnView($user, $subject),
            VoterAttribute::TopicCreate->value => $this->voteOnCreate($user, $subject),
            VoterAttribute::TopicEdit->value,
            VoterAttribute::TopicDelete->value => $this->voteOnEditOrDelete($user, $subject),
            default => false,
        };
    }

    private function voteOnView(?User $user, Topic $topic): bool
    {
        if ($topic->isHidden()) {
            return false;
        }

        $forum = $topic->getForum();
        $canViewForum = $this->security->isGranted(VoterAttribute::ACL->value, [
            'permission' => 'view',
            'entity' => $forum,
        ]);

        if (!$canViewForum) {
            return false;
        }

        $onlyShowOwnTopics = $forum->getDisplaySettings()->isOnlyShowOwnTopics();
        $isOwnTopic = $topic->getCreatedBy()?->getId() === $user->getId();
        if (!$onlyShowOwnTopics || $isOwnTopic) {
            return true;
        }

        return $this->security->isGranted(VoterAttribute::ACL->value, [
            'permission' => 'show_all_topics',
            'entity' => $forum,
        ]);
    }

    private function voteOnCreate(?User $user, Forum $forum): bool
    {
        if ($user === null || !$user->isEmailVerified() || $user->isBanned()) {
            return false;
        }

        return $this->security->isGranted(VoterAttribute::ACL->value, [
            'permission' => 'create_topic',
            'entity' => $forum,
        ]);
    }

    private function voteOnEditOrDelete(?User $user, Topic $topic): bool
    {
        if ($user === null) {
            return false;
        }

        if ($topic->getCreatedBy()?->getId() === $user->getId()) {
            return true;
        }

        return false;
    }
}
