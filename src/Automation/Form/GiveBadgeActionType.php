<?php

declare(strict_types=1);

namespace Forumify\Automation\Form;

use Forumify\Core\Form\CodeEditorType;
use Forumify\Forum\Entity\Badge;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class GiveBadgeActionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('badge', EntityType::class, [
                'class' => Badge::class,
                'autocomplete' => true,
                'choice_label' => 'name'
            ])
            ->add('recipient', CodeEditorType::class, [
                'help' => 'admin.automations.action.badge.recipient_help',
                'help_html' => true,
                'density' => 'compact',
            ])
        ;
    }
}
