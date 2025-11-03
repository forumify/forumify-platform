<?php

declare(strict_types=1);

namespace Forumify\Automation\Form;

use Forumify\Core\Form\CodeEditorLanguage;
use Forumify\Core\Form\CodeEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class MessageActionType extends AbstractType
{
    /**
     * @param FormBuilderInterface<array<string, mixed>|null> $builder
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('recipients', CodeEditorType::class, [
                'help' => 'admin.automations.action.recipients_help',
                'help_html' => true,
                'density' => 'compact',
            ])
            ->add('title', CodeEditorType::class, [
                'language' => CodeEditorLanguage::Twig->value,
                'help' => 'admin.automations.action.message.title_help',
                'help_html' => true,
                'density' => 'compact',
            ])
            ->add('message', CodeEditorType::class, [
                'language' => CodeEditorLanguage::Twig->value,
                'help' => 'admin.automations.action.message.message_help',
                'help_html' => true,
            ])
        ;
    }
}
