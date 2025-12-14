<?php

declare(strict_types=1);

namespace Forumify\Admin\EventSubscriber;

use Forumify\Admin\Crud\Event\PostSaveCrudEvent;
use Forumify\Admin\Crud\Event\PreSaveCrudEvent;
use Forumify\Core\Entity\Notification;
use Forumify\Core\Entity\Role;
use Forumify\Core\Entity\User;
use Forumify\Core\Notification\NotificationService;
use Forumify\Core\Repository\RoleRepository;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Core\Service\MediaService;
use Forumify\Forum\Entity\Badge;
use Forumify\Forum\Notification\NewBadgeNotificationType;
use Forumify\Forum\Repository\BadgeRepository;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;

class UserCrudSubscriber implements EventSubscriberInterface
{
    /** @var array<int, array<Badge>> */
    private array $preSaveBadges = [];

    public function __construct(
        private readonly MediaService $mediaService,
        private readonly FilesystemOperator $avatarStorage,
        private readonly RoleRepository $roleRepository,
        private readonly BadgeRepository $badgeRepository,
        private readonly Security $security,
        private readonly NotificationService $notificationService,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PreSaveCrudEvent::getName(User::class) => 'preSaveUser',
            PostSaveCrudEvent::getName(User::class) => 'postSaveUser',
        ];
    }

    /**
     * @param PreSaveCrudEvent<User> $event
     */
    public function preSaveUser(PreSaveCrudEvent $event): void
    {
        $user = $event->getEntity();
        $form = $event->getForm();

        $this->preSaveBadges[$user->getId()] = $this->badgeRepository
            ->createQueryBuilder('b')
            ->innerJoin('b.users', 'u')
            ->where('u = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        $this->keepDisabledRoles($user);
        $this->saveNewAvatar($user, $form);
    }

    /**
     * @param PostSaveCrudEvent<User> $event
     */
    public function postSaveUser(PostSaveCrudEvent $event): void
    {
        $user = $event->getEntity();
        $preSaveBadgeIds = array_map(fn (Badge $badge) => $badge->getId(), $this->preSaveBadges[$user->getId()]);
        foreach ($user->getBadges() as $badge) {
            if (!in_array($badge->getId(), $preSaveBadgeIds, true)) {
                $this->notificationService->sendNotification(new Notification(
                    NewBadgeNotificationType::TYPE,
                    $user,
                    ['badge' => $badge],
                ));
            }
        }
    }

    private function keepDisabledRoles(User $user): void
    {
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

    /**
     * @param User $user
     * @param FormInterface<mixed> $form
     * @return void
     */
    private function saveNewAvatar(User $user, FormInterface $form): void
    {
        if (!$form->has('newAvatar')) {
            return;
        }

        $newAvatar = $form->get('newAvatar')->getData();
        if ($newAvatar === null) {
            return;
        }

        $avatar = $this->mediaService->saveToFilesystem($this->avatarStorage, $newAvatar);
        $user->setAvatar($avatar);
    }
}
