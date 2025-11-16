<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Forumify\OAuth\Entity\OAuthClient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<OAuthClient>
 */
class OAuthClientType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OAuthClient::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isNew = ($options['data'] ?? null) === null;

        $builder->add('name', TextType::class);

        if (!$isNew) {
            $builder->add('clientId', TextType::class);
            $builder->add('clientSecret', TextType::class);
        }

        $builder->add('roleEntities', UserRoleType::class, [
                'label' => 'Roles',
                'required' => false,
                'multiple' => true,
                'autocomplete' => true,
        ]);
    }
}
