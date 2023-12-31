<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Forumify\Core\Repository\SettingRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class SettingsType extends AbstractType
{
    public function __construct(private readonly SettingRepository $settingRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'data' => $this->settingRepository->get('forum.title'),
            ])
            ->add('logo', FileType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Image(
                        maxSize: '10M',
                    ),
                ],
            ])
            ->add('default_avatar', FileType::class, [
                'required' => false,
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
                'help' => 'Protect your forum against spammers. Configure your site on the <a href="https://www.google.com/recaptcha/admin" target="_blank">reCAPTCHA admin console</a>',
                'help_html' => true,
            ])
            ->add('recaptcha_site_key', TextType::class, [
                'data' => $this->settingRepository->get('core.recaptcha.site_key'),
                'label' => 'reCAPTCHA site key'
            ])
            ->add('recaptcha_site_secret', TextType::class, [
                'data' => $this->settingRepository->get('core.recaptcha.site_secret'),
                'label' => 'reCAPTCHA site secret'
            ]);
    }
}
