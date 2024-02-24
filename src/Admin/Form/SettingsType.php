<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Forumify\Core\Repository\SettingRepository;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class SettingsType extends AbstractType
{
    public function __construct(
        private readonly SettingRepository $settingRepository,
        private readonly Packages $packages,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'data' => $this->settingRepository->get('forum.title'),
            ])
            ->add('logo', FileType::class, [
                'required' => false,
                'attr' => [
                    'preview' => ($logo = $this->settingRepository->get('forum.logo'))
                        ? $this->packages->getUrl($logo, 'forumify.asset')
                        : null,
                ],
                'constraints' => [
                    new Assert\Image(
                        maxSize: '10M',
                    ),
                ],
            ])
            ->add('default_avatar', FileType::class, [
                'required' => false,
                'attr' => [
                    'preview' => ($avatar = $this->settingRepository->get('forum.default_avatar'))
                        ? $this->packages->getUrl($avatar, 'forumify.avatar')
                        : null,
                ],
                'constraints' => [
                    new Assert\Image(
                        maxSize: '10M',
                    ),
                ],
            ])
            ->add('enable_registrations', CheckboxType::class, [
                'data' => (bool)$this->settingRepository->get('core.enable_registrations'),
                'required' => false,
            ])
            ->add('enable_recaptcha', CheckboxType::class, [
                'data' => (bool)$this->settingRepository->get('core.recaptcha.enabled'),
                'required' => false,
                'label' => 'Enable Google reCAPTCHA',
                'help' => 'Protect your forum against spammers. Configure your site on the <a href="https://www.google.com/recaptcha/admin" target="_blank">reCAPTCHA admin console</a>.',
                'help_html' => true,
            ])
            ->add('recaptcha_site_key', TextType::class, [
                'data' => $this->settingRepository->get('core.recaptcha.site_key'),
                'label' => 'reCAPTCHA site key',
                'required' => false,
            ])
            ->add('recaptcha_site_secret', TextType::class, [
                'data' => $this->settingRepository->get('core.recaptcha.site_secret'),
                'label' => 'reCAPTCHA site secret',
                'required' => false,
            ]);
    }
}
