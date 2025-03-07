<?php

declare(strict_types=1);

namespace Forumify\Core\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<string>
 */
class CodeEditorType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $supportedLanguages = array_column(CodeEditorLanguage::cases(), 'value');

        $resolver->setDefaults([
            'language' => null,
            'density' => null,
        ]);
        $resolver->setAllowedValues('language', [null, ...$supportedLanguages]);
        $resolver->setAllowedValues('density', [null, 'default', 'compact', 'fullscreen']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['language'] = $options['language'] ?? null;
        $view->vars['density'] = $options['density'] ?? null;
    }

    public function getParent(): string
    {
        return TextareaType::class;
    }
}
