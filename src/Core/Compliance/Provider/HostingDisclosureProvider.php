<?php

declare(strict_types=1);

namespace Forumify\Core\Compliance\Provider;

use Forumify\Core\Compliance\Disclosure\RecipientDisclosure;
use Forumify\Core\Compliance\PrivacyDisclosureProviderInterface;
use Forumify\Core\Repository\SettingRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class HostingDisclosureProvider implements PrivacyDisclosureProviderInterface
{
    public const string SETTING_HOSTING_PROVIDER = 'forumify.compliance.hosting_provider';

    public function __construct(
        private readonly SettingRepository $settingRepository,
        #[Autowire(env: 'bool:FORUMIFY_HOSTED_INSTANCE')]
        private readonly bool $isHostedInstance,
    ) {
    }

    public function getDisclosures(): iterable
    {
        if ($this->isHostedInstance) {
            yield new RecipientDisclosure(
                'forumify',
                'privacy_policy.recipient.forumify_cloud.purpose',
                ['privacy_policy.field.all_platform_data'],
                false,
                'https://forumify.net/privacy-policy',
            );

            return;
        }

        $hostingProvider = $this->settingRepository->get(self::SETTING_HOSTING_PROVIDER);
        if (is_string($hostingProvider) && $hostingProvider !== '') {
            yield new RecipientDisclosure(
                $hostingProvider,
                'privacy_policy.recipient.hosting.purpose',
                ['privacy_policy.field.all_platform_data'],
            );
        }
    }
}
