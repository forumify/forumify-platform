<?php

declare(strict_types=1);

namespace Forumify\Admin\EventSubscriber;

use Forumify\Admin\Crud\Event\PreSaveCrudEvent;
use Forumify\Core\Service\MediaService;
use Forumify\Forum\Entity\Badge;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BadgeCrudSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MediaService $mediaService,
        private readonly FilesystemOperator $assetStorage,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [PreSaveCrudEvent::getName(Badge::class) => 'preSaveBadge'];
    }

    /**
     * @param PreSaveCrudEvent<Badge> $event
     * @return void
     */
    public function preSaveBadge(PreSaveCrudEvent $event): void
    {
        $badge = $event->getEntity();
        $form = $event->getForm();

        $newImage = $form->get('newImage')->getData();
        if (!($newImage instanceof UploadedFile)) {
            return;
        }

        $image = $this->mediaService->saveToFilesystem($this->assetStorage, $newImage);
        $badge->setImage($image);
    }
}
