<?php

declare(strict_types=1);

namespace Forumify\Forum\Form;

use Forumify\Core\Entity\User;
use Forumify\Core\Form\RichTextEditorType;
use Forumify\Core\Form\UserNotificationSettingsType;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
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
    public function __construct(private readonly Packages $packages)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $avatarPreview = $options['data']?->getAvatar();

        $builder
            ->add('displayName', TextType::class, [
                'constraints' => [new Assert\Length(min: 4, max: 32, normalizer: 'trim')],
            ])
            ->add('newAvatar', FileType::class, [
                'required' => false,
                'label' => 'Avatar',
                'attr' => [
                    'preview' => $avatarPreview
                        ? $this->packages->getUrl($avatarPreview, 'forumify.avatar')
                        : null,
                ],
                'constraints' => [
                    new Assert\Image(
                        maxSize: '10M',
                    ),
                ],
                'mapped' => false,
            ])
            ->add('signature', RichTextEditorType::class, [
                'required' => false,
            ])
            ->add('timezone', TimezoneType::class, ['autocomplete' => true])
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
