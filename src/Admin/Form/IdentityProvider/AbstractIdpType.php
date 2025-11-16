<?php

declare(strict_types=1);

namespace Forumify\Admin\Form\IdentityProvider;

use Forumify\OAuth\Entity\IdentityProvider;
use Forumify\OAuth\Idp\AbstractIdp;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<AbstractIdp>
 */
class AbstractIdpType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('idp', null);
        $resolver->setAllowedTypes('idp', IdentityProvider::class);
    }
}
