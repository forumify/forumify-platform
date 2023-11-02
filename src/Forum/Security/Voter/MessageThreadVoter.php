<?php

declare(strict_types=1);

namespace Forumify\Forum\Security\Voter;

use Forumify\Core\Entity\User;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\MessageThread;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MessageThreadVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
       return $attribute === VoterAttribute::MessageThreadView->value && $subject instanceof MessageThread;
    }

    /**
     * @param MessageThread $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User|null $user */
        $user = $token->getUser();
        if ($user === null) {
            return false;
        }

        /** @var User $participant */
        foreach ($subject->getParticipants() as $participant) {
            if ($participant->getId() === $user->getId()) {
                return true;
            }
        }

        return false;
    }
}
