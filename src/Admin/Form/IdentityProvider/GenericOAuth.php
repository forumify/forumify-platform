<?php

declare(strict_types=1);

namespace Forumify\Admin\Form\IdentityProvider;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class GenericOAuth extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('clientId', TextType::class)
            ->add('clientSecret', TextType::class)
        ;
    }
}
