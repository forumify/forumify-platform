<?php

declare(strict_types=1);

namespace Forumify\Forum\Security\Voter;

use Forumify\Core\Entity\User;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\MessageThread;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, MessageThread|null>
 */
class MessageThreadVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
            VoterAttribute::MessageThreadCreate->value,
            VoterAttribute::MessageThreadView->value,
            VoterAttribute::MessageThreadReply->value,
        ], true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        return match ($attribute) {
            VoterAttribute::MessageThreadCreate->value => $user->isEmailVerified() && !$user->isBanned(),
            VoterAttribute::MessageThreadView->value => $this->voteOnView($subject, $user),
            VoterAttribute::MessageThreadReply->value => $this->voteOnReply($subject, $user),
            default => false,
        };
    }

    /**
     * User must be a participant of the thread in order to view it
     */
    private function voteOnView(MessageThread $subject, User $user): bool
    {
        /** @var User $participant */
        foreach ($subject->getParticipants() as $participant) {
            if ($participant->getId() === $user->getId()) {
                return true;
            }
        }

        return false;
    }

    public function voteOnReply(MessageThread $subject, User $user): bool
    {
        return $user->isEmailVerified() && !$user->isBanned() && $this->voteOnView($subject, $user);
    }
}
