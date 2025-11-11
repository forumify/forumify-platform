<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Forumify\Core\Entity\Theme;
use Forumify\Core\Form\CodeEditorLanguage;
use Forumify\Core\Form\CodeEditorType;
use Forumify\Plugin\AbstractForumifyTheme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ThemeType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Theme::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Theme $theme */
        $theme = $options['data'];
        /** @var AbstractForumifyTheme $plugin */
        $plugin = $theme->getPlugin()->getPlugin();

        $builder
            ->add('name', TextType::class, [
                'help' => 'admin.theme.name_help',
            ])
            ->add('active', CheckboxType::class, [
                'required' => false,
                'help' => 'admin.theme.active_help',
            ])
            ->add('themeConfig', ThemeConfigType::class, [
                'label' => false,
                'config' => $plugin->getThemeConfig(),
                'data' => $theme->getThemeConfig(),
            ])
            ->add('css', CodeEditorType::class, [
                'label' => 'admin.theme.css',
                'language' => CodeEditorLanguage::Css->value,
                'required' => false,
                'empty_data' => '',
            ]);
    }
}
