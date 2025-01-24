<?php

declare(strict_types=1);

namespace Forumify\Automation\Form;

use Forumify\Core\Form\CodeEditorLanguage;
use Forumify\Core\Form\CodeEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class MessageActionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('recipients', CodeEditorType::class, [
                'help' => 'admin.automations.action.message.recipients_help',
                'help_html' => true,
                'compact' => true,
            ])
            ->add('title', CodeEditorType::class, [
                'language' => CodeEditorLanguage::Twig->value,
                'help' => 'admin.automations.action.message.title_help',
                'help_html' => true,
                'compact' => true,
            ])
            ->add('message', CodeEditorType::class, [
                'language' => CodeEditorLanguage::Twig->value,
                'help' => 'admin.automations.action.message.message_help',
                'help_html' => true,
            ])
        ;
    }
}
