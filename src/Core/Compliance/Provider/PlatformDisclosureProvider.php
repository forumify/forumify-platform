<?php

declare(strict_types=1);

namespace Forumify\Core\Compliance\Provider;

use Forumify\Core\Compliance\Disclosure\CookieCategory;
use Forumify\Core\Compliance\Disclosure\CookieDisclosure;
use Forumify\Core\Compliance\Disclosure\DataDisclosure;
use Forumify\Core\Compliance\Disclosure\LegalBasis;
use Forumify\Core\Compliance\PrivacyDisclosureProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class PlatformDisclosureProvider implements PrivacyDisclosureProviderInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function getDisclosures(): iterable
    {
        yield new DataDisclosure(
            'privacy_policy.data.account.category',
            [
                'privacy_policy.field.username',
                'privacy_policy.field.email',
                'privacy_policy.field.password',
                'privacy_policy.field.display_name',
                'privacy_policy.field.avatar',
                'privacy_policy.field.signature',
                'privacy_policy.field.language',
                'privacy_policy.field.timezone',
                'privacy_policy.field.roles',
            ],
            'privacy_policy.data.account.purpose',
            LegalBasis::Contract,
            'privacy_policy.retention.account',
        );

        yield new DataDisclosure(
            'privacy_policy.data.content.category',
            [
                'privacy_policy.field.topics',
                'privacy_policy.field.comments',
                'privacy_policy.field.reactions',
                'privacy_policy.field.messages',
                'privacy_policy.field.uploads',
            ],
            'privacy_policy.data.content.purpose',
            LegalBasis::Contract,
            'privacy_policy.retention.content',
        );

        yield new DataDisclosure(
            'privacy_policy.data.activity.category',
            [
                'privacy_policy.field.read_markers',
                'privacy_policy.field.subscriptions',
                'privacy_policy.field.last_activity',
                'privacy_policy.field.notifications',
            ],
            'privacy_policy.data.activity.purpose',
            LegalBasis::LegitimateInterests,
            'privacy_policy.retention.activity',
        );

        yield new DataDisclosure(
            'privacy_policy.data.technical.category',
            ['privacy_policy.field.ip_address', 'privacy_policy.field.user_agent'],
            'privacy_policy.data.technical.purpose',
            LegalBasis::LegitimateInterests,
            'privacy_policy.retention.server_logs',
        );

        yield new DataDisclosure(
            'privacy_policy.data.audit.category',
            ['privacy_policy.field.audit_changes'],
            'privacy_policy.data.audit.purpose',
            LegalBasis::LegitimateInterests,
            'privacy_policy.retention.audit',
        );

        yield new CookieDisclosure(
            $this->getSessionCookieName(),
            'privacy_policy.cookie.session.purpose',
            'privacy_policy.cookie.lifetime.session',
            CookieCategory::Necessary,
        );

        yield new CookieDisclosure(
            'REMEMBERME',
            'privacy_policy.cookie.remember_me.purpose',
            'privacy_policy.cookie.lifetime.remember_me',
            CookieCategory::Functional,
        );

        yield new CookieDisclosure(
            'forumify_theme',
            'privacy_policy.cookie.theme.purpose',
            'privacy_policy.cookie.lifetime.year_1',
            CookieCategory::Functional,
        );
    }

    private function getSessionCookieName(): string
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null || !$request->hasSession()) {
            return 'sessid';
        }

        return $request->getSession()->getName();
    }
}
