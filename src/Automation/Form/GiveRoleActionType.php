<?php

declare(strict_types=1);

namespace Forumify\Automation\Form;

use Forumify\Core\Entity\Role;
use Forumify\Core\Form\CodeEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class GiveRoleActionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('role', EntityType::class, [
                'class' => Role::class,
                'autocomplete' => true,
                'choice_label' => 'title',
            ])
            ->add('recipient', CodeEditorType::class, [
                'help' => 'admin.automations.action.role.recipient_help',
                'help_html' => true,
                'density' => 'compact',
            ])
        ;
    }
}
