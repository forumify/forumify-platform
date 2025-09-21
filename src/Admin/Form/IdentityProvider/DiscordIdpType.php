<?php

declare(strict_types=1);

namespace Forumify\Admin\Form\IdentityProvider;

use Forumify\Core\Form\InfoType;
use Forumify\Core\Form\PreType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DiscordIdpType extends AbstractIdpType
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $idpSlug = $options['idp']->getSlug();

        $builder
            ->add('instructions', InfoType::class, [
                'help' => 'admin.identity_provider.discord.instructions',
            ])
            ->add('clientId', TextType::class)
            ->add('clientSecret', TextType::class)
            ->add('redirectUri', PreType::class, [
                'data' => $this->urlGenerator->generate('forumify_oauth_idp_callback', [
                    'slug' => $idpSlug,
                ], UrlGeneratorInterface::ABSOLUTE_URL),
            ])
        ;
    }
}
