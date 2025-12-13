<?php

declare(strict_types=1);

namespace Forumify\Core\Form;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ObjectManager;
use Forumify\Core\Entity\SortableEntityInterface;
use Symfony\Bridge\Doctrine\Form\ChoiceList\ORMQueryBuilderLoader;
use Symfony\Bridge\Doctrine\Form\Type\EntityType as SymfonyEntityType;

class EntityType extends SymfonyEntityType
{
    public function getLoader(ObjectManager $manager, object $queryBuilder, string $class): ORMQueryBuilderLoader
    {
        if (is_a($class, SortableEntityInterface::class, true)) {
            $this->addDefaultSort($queryBuilder);
        }

        return parent::getLoader($manager, $queryBuilder, $class);
    }

    private function addDefaultSort(QueryBuilder $queryBuilder): void
    {
        $rootAlias = $queryBuilder->getRootAliases();
        if (count($rootAlias) !== 1) {
            return;
        }

        $rootAlias = reset($rootAlias);
        $queryBuilder->addOrderBy("$rootAlias.position", 'ASC');
    }
}
