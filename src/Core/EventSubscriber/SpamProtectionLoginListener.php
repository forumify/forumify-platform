<?php

declare(strict_types=1);

namespace Forumify\Core\EventSubscriber;

use Forumify\Core\Service\SpamProtectionService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Login is handled by security and not the login controller
 * So this listener is required to apply recaptcha to the login route
 */
#[AsEventListener(KernelEvents::REQUEST, priority: 10)]
class SpamProtectionLoginListener
{
    public function __construct(
        private readonly SpamProtectionService $spamProtectionService,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if ($request->getMethod() !== 'POST') {
            return;
        }

        if ($request->attributes->get('_route') !== 'forumify_core_login') {
            return;
        }

        if (!$this->spamProtectionService->isBot()) {
            return;
        }

        $session = $request->getSession();
        if ($session instanceof FlashBagAwareSessionInterface) {
            $session->getFlashBag()->add('error', 'flashes.bot_detected');
        }
        $login = $this->urlGenerator->generate('forumify_core_index');
        $event->setResponse(new RedirectResponse($login));
    }
}
