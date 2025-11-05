<?php

declare(strict_types=1);

namespace Forumify\Automation\Form;

use Forumify\Core\Form\CodeEditorLanguage;
use Forumify\Core\Form\CodeEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class WebhookActionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('webhookUrl', TextType::class)
            ->add('data', CodeEditorType::class, [
                'language' => CodeEditorLanguage::Twig->value,
                'help' => 'admin.automations.action.webhook.data_help',
                'help_html' => true,
            ])
        ;
    }
}
