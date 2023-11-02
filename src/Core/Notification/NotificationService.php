<?php

declare(strict_types=1);

namespace Forumify\Core\Notification;

use Doctrine\ORM\EntityManagerInterface;
use Forumify\Core\Entity\Notification;
use Forumify\Core\Repository\NotificationRepository;
use JsonSerializable;
use RuntimeException;
use Symfony\Component\Messenger\MessageBusInterface;

class NotificationService
{
    public function __construct(
        private readonly NotificationRepository $notificationRepository,
        private readonly MessageBusInterface $messageBus,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function sendNotification(Notification $notification): void
    {
        $serializedContext = $this->serializeContext($notification->getContext());
        $notification->setContext($serializedContext);
        $this->notificationRepository->save($notification);

        $this->messageBus->dispatch(new NotificationMessage($notification->getId()));
    }

    public function fetchNotification(int $id): ?Notification
    {
        /** @var Notification|null $notification */
        $notification = $this->notificationRepository->find($id);
        if ($notification === null) {
            return null;
        }

        $deserializedContext = $this->deserializeContext($notification->getContext());
        $notification->setContext($deserializedContext);
        return $notification;
    }

    private function serializeContext(array $context): array
    {
        $newContext = [];
        foreach ($context as $k => $v) {
            if (is_array($v)) {
                $newContext[$k] = $this->serializeContext($v);
                continue;
            }

            if (is_scalar($v)) {
                $newContext[$k] = $v;
                continue;
            }

            if (is_object($v)) {
                if (method_exists($v, 'getId')) {
                    $newContext[$k] = 'entity::' . get_class($v) . '__' . $v->getId();
                    continue;
                }

                if ($v instanceof JsonSerializable) {
                    $newContext[$k] = $v->jsonSerialize();
                    continue;
                }
            }

            throw new RuntimeException("Unable to serialize notification context value at $k");
        }

        return $newContext;
    }

    private function deserializeContext(array $context): array
    {
        $newContext = [];
        foreach ($context as $k => $v) {
            if (is_array($v)) {
                $newContext[$k] = $this->deserializeContext($v);
                continue;
            }

            if (is_string($v) && str_starts_with($v, 'entity::')) {
                [$class, $id] = explode('__', str_replace('entity::', '', $v));
                $repository = $this->entityManager->getRepository($class);
                $newContext[$k] = $repository->find($id);
                continue;
            }

            $newContext[$k] = $v;
        }

        return $newContext;
    }
}
