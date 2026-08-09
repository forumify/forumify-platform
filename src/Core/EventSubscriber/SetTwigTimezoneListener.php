<?php

declare(strict_types=1);

namespace Forumify\Core\EventSubscriber;

use Forumify\Core\Service\TimezoneResolver;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;
use Twig\Extension\CoreExtension;

#[AsEventListener(KernelEvents::REQUEST)]
class SetTwigTimezoneListener
{
    public function __construct(
        private readonly TimezoneResolver $timezoneResolver,
        private readonly Environment $twig
    ) {
    }

    public function __invoke(): void
    {
        $coreExt = $this->twig->getExtension(CoreExtension::class);
        $coreExt->setTimezone($this->timezoneResolver->getTimezone());
    }
}
