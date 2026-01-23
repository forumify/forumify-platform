<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Forumify\Core\Form\ColorPickerType;
use Forumify\Plugin\ThemeConfig;
use Forumify\Plugin\ThemeVar;
use Forumify\Plugin\ThemeVarType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormTypeInterface;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class ThemeVarsType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined('config');
        $resolver->addAllowedTypes('config', ThemeConfig::class);

        $resolver->setDefaults(['mode' => 'default']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        ['config' => $config, 'mode' => $mode, 'data' => $data] = $options;

        /** @var ThemeVar $var */
        foreach ($config->vars as $var) {
            $userData = $data[$var->key] ?? null;
            $defaultData = $mode === 'default' ? $var->defaultValue : $var->defaultDarkValue;

            $fieldOptions = [];
            if ($var->help) {
                $fieldOptions['help'] = $var->help;
            }

            $builder->add($var->key, $this->getFormType($var->type), [
                'label' => "{$var->label} <span class='text-small'>(--{$var->key})</span>",
                'label_html' => true,
                'data' => $userData ?? $defaultData,
                'required' => false,
                ...$fieldOptions,
            ]);
        }
    }

    /**
     * @return class-string<FormTypeInterface<*>>
     */
    private function getFormType(ThemeVarType $type): string
    {
        return match ($type) {
            ThemeVarType::Color => ColorPickerType::class,
            default => TextType::class,
        };
    }
}
