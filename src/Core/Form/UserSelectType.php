<?php

declare(strict_types=1);

namespace Forumify\Core\Form;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

/**
 * @extends AbstractType<User|iterable<User>>
 */
#[AsEntityAutocompleteField(alias: 'forumify_user_select')]
class UserSelectType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => User::class,
            'choice_label' => 'displayName',
            'searchable_fields' => ['username', 'displayName'],
            'security' => 'ROLE_USER',
            'preload' => false,
            'min_characters' => 1,
            'query_builder' => fn (Options $options) => fn (EntityRepository $repository) => $this->createQueryBuilder(
                $repository,
                $options['extra_options'],
            ),
        ]);

        $resolver->setNormalizer('attr', fn (Options $options, array $attr) => [
            'placeholder' => 'user_select_placeholder',
            ...$attr,
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }

    /**
     * @param EntityRepository<User> $repository
     * @param array<string, mixed> $extraOptions
     */
    private function createQueryBuilder(EntityRepository $repository, array $extraOptions): QueryBuilder
    {
        $qb = $repository->createQueryBuilder('u');

        $excludedUserIds = $extraOptions['exclude_users'] ?? [];
        if (!empty($excludedUserIds)) {
            $qb->andWhere('u.id NOT IN (:excludedUserIds)')->setParameter('excludedUserIds', $excludedUserIds);
        }

        if ($extraOptions['active_only'] ?? true) {
            $qb->andWhere('u.banned = 0')->andWhere('u.emailVerified = 1');
        }

        return $qb;
    }
}
