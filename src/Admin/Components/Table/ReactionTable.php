<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Table;

use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Component\Table\AbstractTable;
use Forumify\Forum\Entity\Reaction;
use Forumify\Forum\Repository\ReactionRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('ReactionTable', '@Forumify/components/table/table.html.twig')]
class ReactionTable extends AbstractTable
{
    public function __construct(
        private readonly ReactionRepository $reactionRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    protected function buildTable(): void
    {
        $this
            ->addColumn([
                'name' => 'name',
                'label' => 'Name',
                'field' => 'name',
            ])
            ->addColumn([
                'name' => 'actions',
                'label' => '',
                'searchable' => false,
                'renderer' => [$this, 'renderActionColumn'],
            ]);
    }

    protected function renderActionColumn(Reaction $reaction): string
    {
        $editUrl = $this->urlGenerator->generate('forumify_admin_reaction', ['id' => $reaction->getId()]);
        return '<a class="btn-icon" href="' . $editUrl . '"><i class="ph ph-pencil"></i></a>';
    }

    protected function getData(int $limit, int $offset, array $search): array
    {
        return $this->getQuery($search)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    protected function getTotalCount(array $search): int
    {
        return $this->getQuery($search)
            ->select('COUNT(r.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function getQuery(array $search): QueryBuilder
    {
        $qb = $this->reactionRepository->createQueryBuilder('r');
        foreach ($search as $column => $value) {
            if (!empty($value)) {
                $qb->andWhere("r.$column LIKE :$column");
                $qb->setParameter($column, "%$value%");
            }
        }

        return $qb;
    }
}
