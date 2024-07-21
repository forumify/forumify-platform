<?php

declare(strict_types=1);

namespace Forumify\Core\EventSubscriber;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

#[AsEventListener(KernelEvents::EXCEPTION)]
class ErrorSubscriber
{
    public function __construct(
        #[Autowire('%kernel.environment%')]
        private readonly string $env,
        private readonly Environment $twig
    ) {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        if ($this->env === 'dev') {
            return;
        }

        $exception = $event->getThrowable();
        $statusCode = method_exists($exception, 'getStatusCode')
            ? $exception->getStatusCode()
            : 500;

        $template = $this->twig->render('@Forumify/frontend/error.html.twig', [
            'exception' => $exception,
            'code' => $statusCode,
        ]);

        $response = new Response($template);
        $response->setStatusCode($statusCode);
        $event->setResponse($response);
    }
}