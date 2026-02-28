<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class SpamProtectionService implements SpamProtectionServiceInterface
{
    /**
     * @param iterable<SpamProtectionServiceInterface> $spamProtectionServices
     */
    public function __construct(
        #[AutowireIterator('forumify.spam_protection.service')]
        private readonly iterable $spamProtectionServices,
    ) {
    }

    public function isEnabled(): bool
    {
        // phpcs:ignore
        foreach ($this->getEnabledServices() as $_) {
            return true;
        }
        return false;
    }

    public function isBot(): bool
    {
        foreach ($this->getEnabledServices() as $service) {
            if ($service->isBot()) {
                return true;
            }
        }
        return false;
    }

    public function getJavascripts(string $formId): string
    {
        $javascripts = '';
        foreach ($this->getEnabledServices() as $service) {
            $javascripts .= $service->getJavascripts($formId);
        }
        return $javascripts;
    }

    public function modifyButtonHtml(string $html): string
    {
        foreach ($this->getEnabledServices() as $service) {
            $html = $service->modifyButtonHtml($html);
        }
        return $html;
    }

    /**
     * @return iterable<SpamProtectionServiceInterface>
     */
    private function getEnabledServices(): iterable
    {
        foreach ($this->spamProtectionServices as $service) {
            if ($service->isEnabled()) {
                yield $service;
            }
        }
    }
}
