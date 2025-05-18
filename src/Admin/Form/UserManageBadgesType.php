<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Forumify\Core\Entity\User;
use Forumify\Forum\Entity\Badge;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserManageBadgesType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('badges', EntityType::class, [
            'class' => Badge::class,
            'choice_label' => 'name',
            'required' => false,
            'multiple' => true,
            'autocomplete' => true,
        ]);
    }
}
