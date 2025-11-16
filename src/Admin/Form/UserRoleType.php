<?php

declare(strict_types=1);

namespace Forumify\Admin\Form;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Entity\Role;
use Forumify\Core\Security\VoterAttribute;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class UserRoleType extends AbstractType
{
    public function __construct(private readonly Security $security)
    {
    }

    public function getParent(): string
    {
        return EntityType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => Role::class,
            'options_as_html' => true,
            'choice_label' => $this->getChoiceLabel(...),
            'choice_attr' => $this->getChoiceAttributes(...),
            'query_builder' => $this->getQueryBuilder(...),
        ]);
    }

    /**
     * @param EntityRepository<Role> $er
     * @return QueryBuilder
     */
    private function getQueryBuilder(EntityRepository $er): QueryBuilder
    {
        return $er
            ->createQueryBuilder('r')
            ->andWhere('r.slug != :guest')
            ->andWhere('r.slug != :user')
            ->orderBy('r.position', 'ASC')
            ->setParameter('guest', 'guest')
            ->setParameter('user', 'user')
        ;
    }

    private function getChoiceLabel(Role $role): string
    {
        $title = $role->getTitle();
        if ($this->security->isGranted(VoterAttribute::AssignRole->value, $role)) {
            return $title;
        }

        return "<div class='disabled'>$title</div>";
    }

    /**
     * @param Role $role
     * @return array<string, string>
    */
    private function getChoiceAttributes(Role $role): array
    {
        if ($this->security->isGranted(VoterAttribute::AssignRole->value, $role)) {
            return [];
        }

        return ['disabled' => 'disabled'];
    }
}
