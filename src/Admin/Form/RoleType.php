<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Forumify\Core\Entity\Role;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoleType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Role::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class)
            ->add('description', TextareaType::class)
            ->add('administrator', CheckboxType::class, [
                'required' => false,
                'help' => 'role_type.administrator'
            ])
            ->add('moderator', CheckboxType::class, [
                'required' => false,
                'help' => 'role_type.moderator'
            ]);
    }
}
