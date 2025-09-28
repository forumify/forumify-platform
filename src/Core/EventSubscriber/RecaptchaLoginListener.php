<?php

declare(strict_types=1);

namespace Forumify\Core\EventSubscriber;

use Forumify\Core\Repository\SettingRepository;
use Forumify\Core\Service\RecaptchaService;
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
class RecaptchaLoginListener
{
    public function __construct(
        private readonly SettingRepository $settingRepository,
        private readonly RecaptchaService $recaptchaService,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');
        if ($request->getMethod() !== 'POST' || $route !== 'forumify_core_login') {
            return;
        }

        if (!$this->settingRepository->get('forumify.recaptcha.enabled')) {
            return;
        }

        $score = $this->recaptchaService->verifyRequest($request);
        if ($score >= 0.8) {
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
