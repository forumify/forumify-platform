<?php

declare(strict_types=1);

namespace Forumify\Core\Compliance;

use Forumify\Core\Compliance\Disclosure\CookieDisclosure;
use Forumify\Core\Compliance\Disclosure\DataDisclosure;
use Forumify\Core\Compliance\Disclosure\RecipientDisclosure;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class PrivacyDisclosureRegistry
{
    /** @var array<CookieDisclosure>|null */
    private ?array $cookies = null;
    /** @var array<DataDisclosure>|null */
    private ?array $data = null;
    /** @var array<RecipientDisclosure>|null */
    private ?array $recipients = null;

    /**
     * @param iterable<PrivacyDisclosureProviderInterface> $providers
     */
    public function __construct(
        #[AutowireIterator('forumify.compliance.disclosure_provider')]
        private readonly iterable $providers,
    ) {
    }

    /**
     * @return array<CookieDisclosure>
     */
    public function getCookies(): array
    {
        $this->collect();
        return $this->cookies ?? [];
    }

    /**
     * @return array<DataDisclosure>
     */
    public function getData(): array
    {
        $this->collect();
        return $this->data ?? [];
    }

    /**
     * @return array<RecipientDisclosure>
     */
    public function getRecipients(): array
    {
        $this->collect();
        return $this->recipients ?? [];
    }

    /**
     * @return array<string, array<DataDisclosure>>
     */
    public function getDataGroupedByLegalBasis(): array
    {
        $grouped = [];
        foreach ($this->getData() as $disclosure) {
            $grouped[$disclosure->legalBasis->value][] = $disclosure;
        }

        return $grouped;
    }

    private function collect(): void
    {
        if ($this->cookies !== null) {
            return;
        }

        $this->cookies = [];
        $this->data = [];
        $this->recipients = [];

        foreach ($this->providers as $provider) {
            foreach ($provider->getDisclosures() as $disclosure) {
                if ($disclosure instanceof CookieDisclosure) {
                    $this->cookies[] = $disclosure;
                } elseif ($disclosure instanceof DataDisclosure) {
                    $this->data[] = $disclosure;
                } elseif ($disclosure instanceof RecipientDisclosure) {
                    $this->recipients[] = $disclosure;
                }
            }
        }
    }
}
