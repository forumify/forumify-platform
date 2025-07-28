<?php

declare(strict_types=1);

namespace Forumify\Core\Component\Table;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Repository\AbstractRepository;
use RuntimeException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;

abstract class AbstractDoctrineTable extends AbstractTable
{
    private EntityManagerInterface $entityManager;
    protected Security $security;
    protected AbstractRepository $repository;

    private array $identifiers = [];
    private array $aliases = [];

    protected ?string $permissionReorder = null;

    abstract protected function getEntityClass(): string;

    protected function getData(int $limit, int $offset, array $search, array $sort): array
    {
        $qb = $this->getQuery($search)
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        foreach ($sort as $column => $direction) {
            $qb = $this->addSortBy($qb, $column, $direction);
        }

        return $qb->getQuery()->getResult();
    }

    protected function getTotalCount(array $search): int
    {
        $ids = implode(',', array_map(static fn(string $id) => "e.$id", $this->identifiers));
        return $this->getQuery($search)
            ->select("COUNT($ids)")
            ->getQuery()
            ->getSingleScalarResult();
    }

    protected function getQuery(array $search): QueryBuilder
    {
        $qb = $this->repository->createQueryBuilder('e');
        $this->addJoins($qb);

        foreach ($search as $column => $value) {
            $qb = $this->addSearch($qb, $column, $value);
        }

        return $qb;
    }

    #[Required]
    public function setServices(EntityManagerInterface $em, Security $security): void
    {
        $this->entityManager = $em;
        $repository = $em->getRepository($this->getEntityClass());
        if (!$repository instanceof AbstractRepository) {
            throw new RuntimeException('Your entity must have a repository that extends ' . AbstractRepository::class);
        }
        $this->repository = $repository;

        $this->identifiers = $em->getClassMetadata($this->getEntityClass())->getIdentifier();
        if (empty($this->identifiers)) {
            throw new RuntimeException('Your entity must have at least 1 identifier (#[ORM\Id])');
        }
        $this->security = $security;
    }

    private function addJoins(QueryBuilder $qb): QueryBuilder
    {
        foreach ($this->getColumns() as $columnName => $column) {
            $field = $column['field'] ?? $columnName;
            $property = explode('.', str_replace('?', '', $field));

            if (count($property) === 1) {
                $this->aliases[$columnName] = 'e.' . reset($property);
                continue;
            }

            if (count($property) === 2) {
                [$join, $property] = $property;
                $alias = uniqid($join . '_');
                $qb->leftJoin("e.$join", $alias);
                $this->aliases[$columnName] = "$alias.$property";
                continue;
            }

            throw new RuntimeException('Having properties nested deeper than 2 is not allowed.');
        }

        return $qb;
    }

    private function addSearch(QueryBuilder $qb, string $column, string $value): QueryBuilder
    {
        $alias = $this->aliases[$column];
        return $qb
            ->andWhere("$alias LIKE :value")
            ->setParameter('value', "%$value%")
        ;
    }

    private function addSortBy(QueryBuilder $qb, string $column, string $direction): QueryBuilder
    {
        $alias = $this->aliases[$column];
        return $qb->addOrderBy($alias, $direction);
    }

    protected function addPositionColumn(string $field = 'position'): static
    {
        $this->sort = ['position' => self::SORT_ASC];
        $this->addColumn('position', [
            'class' => 'w-10',
            'field' => $field,
            'label' => '#',
            'renderer' => $this->renderPositionColumn(),
            'searchable' => false,
        ]);
        return $this;
    }

    protected function renderPositionColumn(): callable
    {
        $metadata = $this->entityManager->getClassMetadata($this->getEntityClass());
        return function ($_, object $entity) use ($metadata): string {
            if (!$this->canReorder($entity)) {
                return '';
            }

            $identifier = $metadata->getIdentifierValues($entity);
            $identifier = reset($identifier);

            return '
                <button
                    class="btn-link btn-small btn-icon p-1"
                    data-action="live#action"
                    data-live-action-param="changePosition"
                    data-live-id-param="' . $identifier . '"
                    data-live-direction-param="down"
                >
                    <i class="ph ph-arrow-down"></i>
                </button>
                <button
                    class="btn-link btn-small btn-icon p-1"
                    data-action="live#action"
                    data-live-action-param="changePosition"
                    data-live-id-param="' . $identifier . '"
                    data-live-direction-param="up"
                >
                    <i class="ph ph-arrow-up"></i>
                </button>
            ';
        };
    }

    #[LiveAction]
    public function changePosition(#[LiveArg] int $id, #[LiveArg] string $direction): void
    {
        $entity = $this->repository->find($id);
        if ($entity === null) {
            return;
        }

        if (!$this->canReorder($entity)) {
            return;
        }

        $this->reorderItem($entity, $direction);
    }

    protected function canReorder(object $entity): bool
    {
        return $this->permissionReorder === null || $this->security->isGranted($this->permissionReorder);
    }

    protected function reorderItem(object $entity, string $direction): void
    {
        $this->repository->reorder($entity, $direction);
    }
}
