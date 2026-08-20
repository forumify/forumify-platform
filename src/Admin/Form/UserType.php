<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Forumify\Core\Entity\User;
use Forumify\Forum\Entity\Badge;
use Forumify\Core\Form\EntityType;
use Forumify\Core\Form\UploadType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends AbstractType<User>
 */
class UserType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class)
            ->add('displayName', TextType::class)
            ->add('email', TextType::class)
            ->add('timezone', TimezoneType::class, [
                'required' => false,
                'autocomplete' => true,
                'placeholder' => 'account_settings.timezone_placeholder',
            ])
            ->add('avatar', UploadType::class, [
                'label' => 'Avatar',
                'required' => false,
                'filesystem' => 'avatar.storage',
                'asset_package' => 'forumify.avatar',
                'accept' => 'image/*',
                'file_constraints' => [new Assert\Image(maxSize: '10M')],
            ])
            ->add('roleEntities', UserRoleType::class, [
                'label' => 'Roles',
                'required' => false,
                'multiple' => true,
                'autocomplete' => true,
            ])
            ->add('badges', EntityType::class, [
                'class' => Badge::class,
                'choice_label' => 'name',
                'required' => false,
                'multiple' => true,
                'autocomplete' => true,
            ]);
    }
}
