<?php

declare(strict_types=1);

namespace Forumify\Forum\Form;

use Forumify\Core\Entity\User;
use Forumify\Core\Form\UserNotificationSettingsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class AccountSettingsType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('newAvatar', FileType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Image(
                        maxSize: '10M',
                    ),
                ],
                'mapped' => false,
            ])
            ->add('notificationSettings', UserNotificationSettingsType::class, [
                'label' => false,
            ]);
    }
}
