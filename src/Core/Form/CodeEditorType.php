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

        $resolver->setDefault('language', null);
        $resolver->setAllowedValues('language', [null, ...$supportedLanguages]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['language'] = $options['language'] ?? null;
    }

    public function getParent(): string
    {
        return TextareaType::class;
    }
}
