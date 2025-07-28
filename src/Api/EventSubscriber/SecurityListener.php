<?php

declare(strict_types=1);

namespace Forumify\Api\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * As a bundle we are not allowed to modify the firewalls or add paths in security.yaml.
 * But we can use a request listener to prematurely end the request with a custom response.
 */
#[AsEventListener(KernelEvents::REQUEST, priority: EventPriorities::PRE_READ)]
class SecurityListener
{
    public function __construct(private readonly Security $security)
    {
    }

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $operation = $request->attributes->get('_api_operation');
        if ($operation === null) {
            return;
        }

        $user = $this->security->getUser();
        if ($user === null) {
            $event->setResponse(new JsonResponse([
                'error' => 'Full authentication is required to access the API.',
            ], Response::HTTP_UNAUTHORIZED));
        }
    }
}
