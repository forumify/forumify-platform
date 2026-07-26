<?php

declare(strict_types=1);

namespace Forumify\Forum\Security\Voter;

use Forumify\Core\Entity\AuthorizableInterface;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\SettingRepository;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\MessageThread;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, MessageThread|null>
 */
class MessageThreadVoter extends Voter
{
    public function __construct(
        private readonly SettingRepository $settingRepository,
        private readonly Security $security,
    ) {
    }

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
        if (!$user instanceof AuthorizableInterface) {
            return false;
        }

        return match ($attribute) {
            VoterAttribute::MessageThreadCreate->value => $this->isVerified($user),
            VoterAttribute::MessageThreadView->value => $this->voteOnView($subject, $user),
            VoterAttribute::MessageThreadReply->value => $this->voteOnReply($subject, $user),
            default => false,
        };
    }

    /**
     * User must be a participant of the thread in order to view it
     */
    private function voteOnView(?MessageThread $subject, AuthorizableInterface $user): bool
    {
        if ($subject === null) {
            return false;
        }

        /** @var User $participant */
        foreach ($subject->getParticipants() as $participant) {
            if ($participant->getId() === $user->getUserId()) {
                return true;
            }
        }

        return false;
    }

    public function voteOnReply(?MessageThread $subject, AuthorizableInterface $user): bool
    {
        return $subject !== null
            && $this->isVerified($user)
            && $this->voteOnView($subject, $user);
    }

    private function isVerified(AuthorizableInterface $user): bool
    {
        if ($this->settingRepository->get('forumify.readonly') && !$this->security->isGranted(VoterAttribute::SuperAdmin->value)) {
            return false;
        }

        if (!$user instanceof User) {
            return true;
        }

        return $user->isEmailVerified() && !$user->isBanned();
    }
}
