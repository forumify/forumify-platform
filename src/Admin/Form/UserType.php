<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Entity\Role;
use Forumify\Core\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('roleEntities', EntityType::class, [
                'class' => Role::class,
                'choice_label' => 'title',
                'multiple' => true,
                'autocomplete' => true,
                'query_builder' => $this->getRoleQueryBuilder(...),
            ]);
    }

    private function getRoleQueryBuilder(EntityRepository $er): QueryBuilder
    {
        return $er
            ->createQueryBuilder('r')
            ->andWhere('r.slug != :guest')
            ->andWhere('r.slug != :user')
            ->setParameters([
                'guest' => 'guest',
                'user' => 'user',
            ]);
    }
}
