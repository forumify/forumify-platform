<?php

declare(strict_types=1);

namespace Forumify\Api\EventSubscriber;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\HttpOperation;
use Forumify\Core\Security\VoterAttribute;
use LogicException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

#[AsEventListener(KernelEvents::REQUEST)]
class AccessControlListListener
{
    public function __construct(
        private readonly PropertyAccessorInterface $propertyAccessor,
        private readonly Security $security,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        $attributes = $event->getRequest()->attributes;
        $operation = $attributes->get('_api_operation');
        if (!$operation instanceof HttpOperation || $operation instanceof GetCollection) {
            return;
        }

        $acl = $operation->getExtraProperties()['acl'] ?? null;
        if ($acl === null) {
            return;
        }

        $entity = $attributes->get('data');
        if ($entity === null) {
            throw new LogicException('NOT IMPLEMENTED');
        }

        if (is_array($acl)) {
            ['permission' => $permission, 'entity' => $pathToEntity] = $acl;
            $entity = $this->propertyAccessor->getValue($entity, $pathToEntity);
        } else {
            $permission = $acl;
        }

        $isAllowed = $this->security->isGranted(VoterAttribute::ACL->value, [
            'permission' => $permission,
            'entity' => $entity,
        ]);
        if ($isAllowed) {
            return;
        }

        $event->setResponse(new JsonResponse([
            'error' => 'Operation not allowed.',
        ], Response::HTTP_FORBIDDEN));
    }
}
