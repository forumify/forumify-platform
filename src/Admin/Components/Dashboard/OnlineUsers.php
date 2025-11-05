<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Dashboard;

use DateInterval;
use DateTime;
use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Component\List\AbstractDoctrineList;
use Forumify\Core\Repository\UserRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('Forumify\\Admin\\OnlineUsers', '@Forumify/admin/dashboard/components/users.html.twig')]
class OnlineUsers extends AbstractDoctrineList
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    public function getTitle(): string
    {
        return 'admin.dashboard.online_users';
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->getQuery();
    }

    protected function getCount(): int
    {
        return (int) $this->getQuery()
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function getQuery(): QueryBuilder
    {
        $min = (new DateTime())->sub(new DateInterval('PT5M'));
        return $this->userRepository
            ->createQueryBuilder('u')
            ->where('u.lastActivity > :min')
            ->setParameter('min', $min)
            ->orderBy('u.lastActivity', 'desc')
        ;
    }
}
