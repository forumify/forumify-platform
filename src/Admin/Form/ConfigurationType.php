<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Symfony\Component\Asset\Packages;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ConfigurationType extends AbstractType
{
    public function __construct(
        private readonly Packages $packages,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('forumify__title', TextType::class, [
                'label' => 'admin.configuration.forum_title',
            ])
            ->add('logo', FileType::class, [
                'label' => 'admin.configuration.logo',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'preview' => ($logo = $options['data']['forumify__logo'] ?? null) !== null
                        ? $this->packages->getUrl($logo, 'forumify.asset')
                        : null,
                ],
                'constraints' => [
                    new Assert\Image(
                        maxSize: '10M',
                    ),
                ],
            ])
            ->add('defaultAvatar', FileType::class, [
                'label' => 'admin.configuration.default_avatar',
                'help' => 'admin.configuration.default_avatar_help',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'preview' => ($avatar = $options['data']['forumify__default_avatar'] ?? null)
                        ? $this->packages->getUrl($avatar, 'forumify.avatar')
                        : null,
                ],
                'constraints' => [
                    new Assert\Image(
                        maxSize: '10M',
                    ),
                ],
            ])
            ->add('forumify__enable_registrations', CheckboxType::class, [
                'label' => 'admin.configuration.enable_registrations',
                'required' => false,
            ])
            ->add('forumify__hide_usernames', CheckboxType::class, [
                'label' => 'admin.configuration.hide_usernames',
                'required' => false,
            ])
            ->add('forumify__login_method', ChoiceType::class, [
                'label' => 'admin.configuration.login_method',
                'help' => 'admin.configuration.login_method_help',
                'required' => false,
                'choices' => [
                    'Username' => 'username',
                    'Email' => 'email',
                    'Both' => 'both',
                ],
                'placeholder' => null,
            ])
            ->add('forumify__index', TextType::class, [
                'label' => 'admin.configuration.index',
                'help' => 'admin.configuration.index_help',
                'required' => false,
                'empty_data' => ''
            ])
            ->add('forumify__enable_auto_updates', CheckboxType::class, [
                'label' => 'admin.configuration.enable_auto_updates',
                'help' => 'admin.configuration.enable_auto_updates_help',
                'required' => false,
            ])
            ->add('forumify__recaptcha__enabled', CheckboxType::class, [
                'label' => 'admin.configuration.recaptcha_enabled',
                'help' => 'admin.configuration.recaptcha_enabled_help',
                'help_html' => true,
                'required' => false,
            ])
            ->add('forumify__recaptcha__site_key', TextType::class, [
                'label' => 'admin.configuration.recaptcha_site_key',
                'required' => false,
            ])
            ->add('forumify__recaptcha__site_secret', TextType::class, [
                'label' => 'admin.configuration.recaptcha_site_secret',
                'required' => false,
            ]);

        $isCloudInstance = (bool)($_SERVER['FORUMIFY_HOSTED_INSTANCE'] ?? false);
        if (!$isCloudInstance) {
            $builder
                ->add('forumify__mailer__from', TextType::class, [
                    'label' => 'admin.configuration.mailer_from',
                    'help' => 'admin.configuration.mailer_from_help',
                    'required' => false,
                ]);
        }
    }
}
