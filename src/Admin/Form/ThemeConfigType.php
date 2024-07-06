<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Forumify\Plugin\ThemeConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ThemeConfigType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined('config');
        $resolver->addAllowedTypes('config', ThemeConfig::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var ThemeConfig $config */
        $config = $options['config'];
        $builder->add('default', ThemeVarsType::class, [
            'label' => false,
            'config' => $config,
            'data' => $options['data']['default'] ?? [],
        ]);
        if ($config->hasDarkVariant) {
            $builder->add('dark', ThemeVarsType::class, [
                'label' => false,
                'config' => $config,
                'mode' => 'dark',
                'data' => $options['data']['dark'] ?? [],
            ]);
        }
    }
}
