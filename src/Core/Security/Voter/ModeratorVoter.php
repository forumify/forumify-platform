<?php

declare(strict_types=1);

namespace Forumify\Core\Security\Voter;

use Forumify\Core\Entity\AccessControlledEntityInterface;
use Forumify\Core\Entity\ACLParameters;
use Forumify\Core\Entity\User;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\Message;
use Forumify\Forum\Entity\MessageThread;
use Forumify\Forum\Entity\Topic;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ModeratorVoter extends Voter
{
    public function __construct(private readonly Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === VoterAttribute::Moderator->value;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User|null $user */
        $user = $token->getUser();
        if ($user === null || !$this->userIsModerator($user)) {
            return false;
        }

        if ($subject === null) {
            return true;
        }

         $subject = match (get_class($subject)) {
            Topic::class => $subject->getForum(),
            Comment::class => $subject->getTopic()->getForum(),
            default => $subject,
         };

        if (!$subject instanceof AccessControlledEntityInterface) {
            return true;
        }

        return $this->security->isGranted(VoterAttribute::ACL->value, [
            'permission' => 'view',
            'entity' => $subject,
        ]);
    }

    private function userIsModerator(User $user): bool
    {
        foreach ($user->getRoleEntities() as $role) {
            if ($role->isModerator()) {
                return true;
            }
        }
        return false;
    }
}
