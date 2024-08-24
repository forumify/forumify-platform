<?php

declare(strict_types=1);

namespace Forumify\Admin\EventSubscriber;

use Forumify\Admin\Crud\Event\PreSaveCrudEvent;
use Forumify\Core\Entity\Role;
use Forumify\Core\Repository\RoleRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RoleCrudSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RoleRepository $roleRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PreSaveCrudEvent::getName(Role::class) => 'preSaveRole',
        ];
    }

    public function preSaveRole(PreSaveCrudEvent $event): void
    {
        /** @var Role $role */
        $role = $event->getEntity();

        $maxPosition = $this->roleRepository->createQueryBuilder('r')
            ->select('MAX(r.position)')
            ->where('r.system = 0')
            ->getQuery()
            ->getSingleScalarResult();

        $role->setPosition($maxPosition + 1);
    }
}
