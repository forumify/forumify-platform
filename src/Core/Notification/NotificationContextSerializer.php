<?php

declare(strict_types=1);

namespace Forumify\Core\Notification;

use Doctrine\ORM\EntityManagerInterface;
use JsonSerializable;
use RuntimeException;

class NotificationContextSerializer
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param array<mixed> $context
     * @return array<mixed>
     */
    public function serialize(array $context): array
    {
        $newContext = [];
        foreach ($context as $k => $v) {
            if (is_array($v)) {
                $newContext[$k] = $this->serialize($v);
                continue;
            }

            if (is_scalar($v) || $v === null) {
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

    /**
     * @param array<mixed> $context
     * @return array<mixed>
     */
    public function deserialize(array $context): array
    {
        $newContext = [];
        foreach ($context as $k => $v) {
            if (is_array($v)) {
                $newContext[$k] = $this->deserialize($v);
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
