<?php

declare(strict_types=1);

namespace Forumify\Admin\EventSubscriber;

use Forumify\Admin\Crud\Event\PreSaveCrudEvent;
use Forumify\Core\Entity\Role;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\RoleRepository;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Core\Service\MediaService;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;

class UserCrudSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MediaService $mediaService,
        private readonly FilesystemOperator $avatarStorage,
        private readonly RoleRepository $roleRepository,
        private readonly Security $security,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [PreSaveCrudEvent::getName(User::class) => 'preSaveUser'];
    }

    public function preSaveUser(PreSaveCrudEvent $event): void
    {
        $user = $event->getEntity();
        $form = $event->getForm();

        $this->keepDisabledRoles($user);
        $this->saveNewAvatar($user, $form);
    }

    private function keepDisabledRoles(User $user): void
    {
        // Use repository since the old roles are already removed from the user
        /** @var array<Role> $preSaveRoles */
        $preSaveRoles = $this->roleRepository
            ->createQueryBuilder('r')
            ->innerJoin('r.users', 'u')
            ->where('u = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        foreach ($preSaveRoles as $role) {
            if (!$this->security->isGranted(VoterAttribute::AssignRole->value, $role)) {
                $user->getRoleEntities()->add($role);
            }
        }
    }

    private function saveNewAvatar(User $user, FormInterface $form): void
    {
        $newAvatar = $form->get('newAvatar')->getData();
        if ($newAvatar !== null) {
            $avatar = $this->mediaService->saveToFilesystem($this->avatarStorage, $newAvatar);
            $user->setAvatar($avatar);
        }
    }
}
