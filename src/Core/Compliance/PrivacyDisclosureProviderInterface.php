<?php

declare(strict_types=1);

namespace Forumify\Core\Compliance;

use Forumify\Core\Compliance\Disclosure\CookieDisclosure;
use Forumify\Core\Compliance\Disclosure\DataDisclosure;
use Forumify\Core\Compliance\Disclosure\RecipientDisclosure;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('forumify.compliance.disclosure_provider')]
interface PrivacyDisclosureProviderInterface
{
    /**
     * Everything this service processes that has to appear in the privacy policy.
     *
     * Providers are services, so anything that is only processed when a setting is enabled or an
     * entity is configured should only be yielded when that is actually the case.
     *
     * @return iterable<CookieDisclosure|DataDisclosure|RecipientDisclosure>
     */
    public function getDisclosures(): iterable;
}
