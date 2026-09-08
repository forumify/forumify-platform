<?php

declare(strict_types=1);

namespace Forumify\OAuth\Compliance;

use Forumify\Core\Compliance\Disclosure\DataDisclosure;
use Forumify\Core\Compliance\Disclosure\LegalBasis;
use Forumify\Core\Compliance\Disclosure\RecipientDisclosure;
use Forumify\Core\Compliance\PrivacyDisclosureProviderInterface;
use Forumify\OAuth\Idp\IdentityProviderInterface;
use Forumify\OAuth\Compliance\IdentityProviderPrivacyInterface;
use Forumify\OAuth\Repository\IdentityProviderRepository;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class IdentityProviderDisclosureProvider implements PrivacyDisclosureProviderInterface
{
    /**
     * @param iterable<string, IdentityProviderInterface> $idpTypes
     */
    public function __construct(
        private readonly IdentityProviderRepository $identityProviderRepository,
        #[AutowireIterator('forumify.oauth.identity_provider', defaultIndexMethod: 'getType')]
        private readonly iterable $idpTypes,
    ) {
    }

    public function getDisclosures(): iterable
    {
        $identityProviders = $this->identityProviderRepository->findAll();
        if (empty($identityProviders)) {
            return;
        }

        yield new DataDisclosure(
            'privacy_policy.data.linked_accounts.category',
            ['privacy_policy.field.external_identifier', 'privacy_policy.field.external_username'],
            'privacy_policy.data.linked_accounts.purpose',
            LegalBasis::Contract,
            'privacy_policy.retention.until_unlinked',
        );

        $idpTypes = iterator_to_array($this->idpTypes);

        $usedIdpTypes = [];
        foreach ($identityProviders as $identityProvider) {
            $type = $identityProvider->getType();
            $usedIdpTypes[$type] = $idpTypes[$type] ?? null;
        }

        foreach (array_filter($usedIdpTypes) as $type => $idpType) {
            if (!$idpType instanceof IdentityProviderPrivacyInterface) {
                continue;
            }

            yield new RecipientDisclosure(
                ucwords($type),
                'privacy_policy.recipient.identity_provider.purpose',
                ['privacy_policy.field.sign_in_request'],
                $idpType->transfersDataOutsideEea(),
                $idpType->getPrivacyPolicyUrl(),
            );
        }
    }
}
