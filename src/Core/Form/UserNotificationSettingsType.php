<?php

declare(strict_types=1);

namespace Forumify\Core\Form;

use Forumify\Core\Entity\UserNotificationSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserNotificationSettingsType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserNotificationSettings::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('autoSubscribeToOwnTopics', CheckboxType::class, [
                'required' => false,
                'label' => 'user_notification_settings_type.auto_subscribe_to_own_topics_label',
            ])
            ->add('autoSubscribeToTopics', CheckboxType::class, [
                'required' => false,
                'label' => 'user_notification_settings_type.auto_subscribe_to_topics_label',
            ])
            ->add('emailOnMessage', CheckboxType::class, [
                'required' => false,
                'label' => 'user_notification_settings_type.email_on_message_label',
            ])
            ->add('emailOnNotification', CheckboxType::class, [
                'required' => false,
                'label' => 'user_notification_settings_type.email_on_notification_label',
            ]);
    }
}
