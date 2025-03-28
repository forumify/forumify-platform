<?php

declare(strict_types=1);

namespace Forumify\Forum\Security\Voter;

use Forumify\Core\Entity\User;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Entity\Topic;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Comment|Topic>
 */
class CommentVoter extends Voter
{
    public function __construct(private readonly Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
            VoterAttribute::CommentCreate->value,
            VoterAttribute::CommentEdit->value,
            VoterAttribute::CommentDelete->value,
        ], true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        if ($this->security->isGranted(VoterAttribute::Moderator->value, $subject)) {
            return true;
        }

        return match ($attribute) {
            VoterAttribute::CommentCreate->value => $this->voteOnCreate($user, $subject),
            VoterAttribute::CommentEdit->value,
            VoterAttribute::CommentDelete->value => $this->voteOnEditOrDelete($user, $subject),
        };
    }

    private function voteOnCreate(User $user, Topic $topic): bool
    {
        if (!$user->isEmailVerified() || $user->isBanned()) {
            return false;
        }

        if ($topic->isLocked()) {
            return false;
        }

        return $this->security->isGranted(VoterAttribute::ACL->value, [
            'permission' => 'create_comment',
            'entity' => $topic->getForum(),
        ]);
    }

    private function voteOnEditOrDelete(User $user, Comment $comment): bool
    {
        if ($comment->getCreatedBy()?->getId() === $user->getId()) {
            return true;
        }

        return false;
    }
}
