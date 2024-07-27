<?php

declare(strict_types=1);

namespace Forumify\Admin\EventSubscriber;

use Forumify\Admin\Crud\Event\PreSaveCrudEvent;
use Forumify\Core\Entity\User;
use Forumify\Core\Service\MediaService;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserCrudSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MediaService $mediaService,
        private readonly FilesystemOperator $avatarStorage,
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

        $newAvatar = $form->get('newAvatar')->getData();
        if ($newAvatar !== null) {
            $avatar = $this->mediaService->saveToFilesystem($this->avatarStorage, $newAvatar);
            $user->setAvatar($avatar);
        }
    }
}
