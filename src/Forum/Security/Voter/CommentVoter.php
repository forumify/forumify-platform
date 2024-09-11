<?php

declare(strict_types=1);

namespace Forumify\Forum\Security\Voter;

use Forumify\Core\Entity\User;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\Comment;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CommentVoter extends Voter
{
    public function __construct(private readonly Security $security) { }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Comment && in_array($attribute, [
                VoterAttribute::CommentEdit->value,
                VoterAttribute::CommentDelete->value,
            ], true);
    }

    /**
     * @param Comment $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User|null $user */
        $user = $token->getUser();
        if ($user === null) {
            return false;
        }

        if ($attribute === VoterAttribute::CommentEdit->value && $subject->getCreatedBy()?->getId() === $user->getId()) {
            // edit own posts
            return true;
        }

        return $this->security->isGranted(VoterAttribute::Moderator->value, $subject);
    }
}
