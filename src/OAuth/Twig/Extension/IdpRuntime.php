<?php

declare(strict_types=1);

namespace Forumify\OAuth\Twig\Extension;

use Forumify\OAuth\Entity\IdentityProvider;
use Forumify\OAuth\Idp\IdentityProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Twig\Extension\RuntimeExtensionInterface;

class IdpRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        #[AutowireIterator('forumify.oauth.identity_provider', defaultIndexMethod: 'getType')]
        private readonly iterable $idpTypes
    ) {
    }

    public function getIdpButton(IdentityProvider $idp): string
    {
        /** @var array<string, IdentityProviderInterface> $idpTypes */
        $idpTypes = iterator_to_array($this->idpTypes);
        return $idpTypes[$idp->getType()]->getButtonHtml($idp) ?? '';
    }
}
