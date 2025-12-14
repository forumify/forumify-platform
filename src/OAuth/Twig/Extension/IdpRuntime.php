<?php

declare(strict_types=1);

namespace Forumify\OAuth\Twig\Extension;

use Forumify\OAuth\Entity\IdentityProvider;
use Forumify\OAuth\Idp\IdentityProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Twig\Extension\RuntimeExtensionInterface;

class IdpRuntime implements RuntimeExtensionInterface
{
    /**
     * @param iterable<string, IdentityProviderInterface> $idpTypes
     */
    public function __construct(
        #[AutowireIterator('forumify.oauth.identity_provider', defaultIndexMethod: 'getType')]
        private readonly iterable $idpTypes
    ) {
    }

    public function getIdpButton(IdentityProvider $idp): string
    {
        $type = $idp->getType();
        foreach ($this->idpTypes as $key => $idpType) {
            if ($key === $type) {
                return $idpType->getButtonHtml($idp);
            }
        }

        return '';
    }
}
