<?php

declare(strict_types=1);

namespace Forumify\Core\Compliance\Provider;

use Forumify\Core\Compliance\Disclosure\CookieCategory;
use Forumify\Core\Compliance\Disclosure\CookieDisclosure;
use Forumify\Core\Compliance\Disclosure\RecipientDisclosure;
use Forumify\Core\Compliance\PrivacyDisclosureProviderInterface;
use Forumify\Core\Repository\SettingRepository;

class SpamProtectionDisclosureProvider implements PrivacyDisclosureProviderInterface
{
    public function __construct(
        private readonly SettingRepository $settingRepository,
    ) {
    }

    public function getDisclosures(): iterable
    {
        if ($this->settingRepository->get('forumify.recaptcha.enabled')) {
            yield new RecipientDisclosure(
                'Google reCAPTCHA',
                'privacy_policy.recipient.recaptcha.purpose',
                ['privacy_policy.field.ip_address', 'privacy_policy.field.user_agent', 'privacy_policy.field.interaction_data'],
                true,
                'https://policies.google.com/privacy',
            );

            yield new CookieDisclosure(
                '_GRECAPTCHA',
                'privacy_policy.cookie.recaptcha.purpose',
                'privacy_policy.cookie.lifetime.provider_defined',
                CookieCategory::Necessary,
                'Google',
            );
        }

        if (!$this->settingRepository->get('forumify.cf_turnstile.enabled')) {
            return;
        }

        yield new RecipientDisclosure(
            'Cloudflare Turnstile',
            'privacy_policy.recipient.turnstile.purpose',
            ['privacy_policy.field.ip_address', 'privacy_policy.field.user_agent', 'privacy_policy.field.interaction_data'],
            true,
            'https://www.cloudflare.com/privacypolicy/',
        );

        yield new CookieDisclosure(
            'cf_clearance',
            'privacy_policy.cookie.turnstile.purpose',
            'privacy_policy.cookie.lifetime.provider_defined',
            CookieCategory::Necessary,
            'Cloudflare',
        );
    }
}
