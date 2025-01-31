<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Dashboard;

use DateInterval;
use DateTime;
use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Component\List\AbstractDoctrineList;
use Forumify\Core\Repository\UserRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('Forumify\\Admin\\NewUsers', '@Forumify/admin/dashboard/components/users.html.twig')]
class NewUsers extends AbstractDoctrineList
{
    public function __construct(private readonly UserRepository $userRepository)
    {
        $this->size = 8;
    }

    public function getTitle(): string
    {
        return 'admin.dashboard.new_users';
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->getQuery();
    }

    protected function getCount(): int
    {
        return $this->getQuery()
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function getQuery(): QueryBuilder
    {
        $min = (new DateTime())->sub(new DateInterval('P1M'));
        return $this->userRepository
            ->createQueryBuilder('u')
            ->where('u.createdAt > :min')
            ->setParameter('min', $min)
            ->orderBy('u.createdAt', 'desc')
        ;
    }
}
