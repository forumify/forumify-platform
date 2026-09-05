<?php

declare(strict_types=1);

namespace Forumify\Forum\Form;

use Forumify\Core\Entity\User;
use Forumify\Core\Form\RichTextEditorType;
use Forumify\Core\Form\UploadType;
use Forumify\Core\Form\UserNotificationSettingsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends AbstractType<User>
 */
class AccountSettingsType extends AbstractType
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
            ->add('displayName', TextType::class, [
                'constraints' => [new Assert\Length(min: 4, max: 32, normalizer: 'trim')],
            ])
            ->add('avatar', UploadType::class, [
                'label' => 'Avatar',
                'required' => false,
                'filesystem' => 'avatar.storage',
                'asset_package' => 'forumify.avatar',
                'accept' => 'image/*',
                'file_constraints' => [new Assert\Image(maxSize: '10M')],
            ])
            ->add('signature', RichTextEditorType::class, [
                'required' => false,
            ])
            ->add('timezone', TimezoneType::class, [
                'required' => false,
                'autocomplete' => true,
                'placeholder' => 'account_settings.timezone_placeholder',
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => false,
                'first_options' => ['label' => 'New password', 'required' => false],
                'second_options' => ['label' => 'Repeat new password', 'required' => false],
                'constraints' => [new Assert\Length(min: 8)],
            ])
            ->add('notificationSettings', UserNotificationSettingsType::class, [
                'label' => false,
            ]);
    }
}
