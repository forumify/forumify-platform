<?php

declare(strict_types=1);

namespace Forumify\Automation\Form;

use Forumify\Core\Form\CodeEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class ExpressionLangConditionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('expression', CodeEditorType::class, [
                'help_html' => true,
                'help' => 'admin.automations.condition.expression_lang.expression_help',
                'density' => 'compact',
            ])
        ;
    }
}
