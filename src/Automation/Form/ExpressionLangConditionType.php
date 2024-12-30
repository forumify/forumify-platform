<?php

declare(strict_types=1);

namespace Forumify\Automation\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ExpressionLangConditionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('expression', TextType::class, [
                'help_html' => true,
                'help' => 'admin.automations.condition.expression_lang.expression_help'
            ])
        ;
    }
}
