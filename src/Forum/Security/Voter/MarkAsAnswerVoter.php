<?php

declare(strict_types=1);

namespace Forumify\Forum\Security\Voter;

use Forumify\Core\Entity\User;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Core\Service\ACLService;
use Forumify\Forum\Entity\Comment;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Comment>
 */
class MarkAsAnswerVoter extends Voter
{
    public function __construct(private readonly ACLService $aclService)
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

        return $this->aclService->can('moderate', $subject->getTopic()->getForum())
            || $subject->getTopic()->getCreatedBy()?->getId() === $user->getId();
    }
}
