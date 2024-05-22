<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Forumify\Core\Repository\SettingRepository;
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
            ->add('forumify__title', TextType::class)
            ->add('newLogo', FileType::class, [
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
            ->add('newDefaultAvatar', FileType::class, [
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
                'required' => false,
            ])
            ->add('forumify__login_method', ChoiceType::class, [
                'label' => 'Login type',
                'help' => 'Enable login via email, username, or both.',
                'required' => false,
                'choices' => [
                    'Username' => 'username',
                    'Email' => 'email',
                    'Both' => 'both',
                ],
            ])
            ->add('forumify__recaptcha__enabled', CheckboxType::class, [
                'required' => false,
                'label' => 'Enable Google reCAPTCHA',
                'help' => 'Protect your forum against spammers. Configure your site on the <a href="https://www.google.com/recaptcha/admin" target="_blank">reCAPTCHA admin console</a>.',
                'help_html' => true,
            ])
            ->add('forumify__recaptcha__site_key', TextType::class, [
                'label' => 'reCAPTCHA site key',
                'required' => false,
            ])
            ->add('forumify__recaptcha__site_secret', TextType::class, [
                'label' => 'reCAPTCHA site secret',
                'required' => false,
            ]);
    }
}
