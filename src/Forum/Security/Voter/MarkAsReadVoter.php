<?php

declare(strict_types=1);

namespace Forumify\Forum\Security\Voter;

use Forumify\Core\Entity\User;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\Comment;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Comment>
 */
class MarkAsReadVoter extends Voter
{
    public function __construct(private readonly Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === VoterAttribute::CommentMarkAsAnswer->value && $subject instanceof Comment;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            // guests are not allowed
            return false;
        }

        if ($this->security->isGranted(VoterAttribute::Moderator, $subject)) {
            return true;
        }

        return $subject->getTopic()->getCreatedBy()?->getId() === $user->getId();
    }
}
