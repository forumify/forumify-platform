<?php

declare(strict_types=1);

namespace Forumify\Forum\Security\Voter;

use Forumify\Core\Entity\AuthorizableInterface;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\SettingRepository;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Core\Service\ACLService;
use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Entity\Topic;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Comment|Topic>
 */
class CommentVoter extends Voter
{
    public function __construct(
        private readonly ACLService $aclService,
        private readonly SettingRepository $settingRepository,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
            VoterAttribute::CommentView->value,
            VoterAttribute::CommentCreate->value,
            VoterAttribute::CommentEdit->value,
            VoterAttribute::CommentDelete->value,
        ], true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        $user = $user instanceof AuthorizableInterface ? $user->getUser() : null;

        $forum = ($subject instanceof Comment ? $subject->getTopic() : $subject)->getForum();
        if ($this->aclService->can('moderate', $forum)) {
            return true;
        }

        return match ($attribute) {
            VoterAttribute::CommentView->value => $this->aclService->can('view', $forum),
            VoterAttribute::CommentCreate->value => $subject instanceof Topic && $this->voteOnCreate($user, $subject),
            VoterAttribute::CommentEdit->value,
            VoterAttribute::CommentDelete->value => $subject instanceof Comment && $this->voteOnEditOrDelete($user, $subject),
            default => false,
        };
    }

    private function voteOnCreate(?User $user, Topic $topic): bool
    {
        if ($user === null || !$user->isEmailVerified() || $user->isBanned() || $topic->isLocked()) {
            return false;
        }

        if ($this->settingRepository->get('forumify.readonly')) {
            return false;
        }

        return $this->aclService->can('create_comment', $topic->getForum());
    }

    private function voteOnEditOrDelete(?User $user, Comment $comment): bool
    {
        if ($user === null) {
            return false;
        }

        if ($this->settingRepository->get('forumify.readonly')) {
            return false;
        }

        return $comment->getCreatedBy()?->getId() === $user->getId();
    }
}
