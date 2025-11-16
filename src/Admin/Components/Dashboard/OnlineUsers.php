<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Dashboard;

use DateInterval;
use DateTime;
use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Component\List\AbstractDoctrineList;
use Forumify\Core\Entity\User;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

/**
 * @extends AbstractDoctrineList<User>
 */
#[AsLiveComponent('Forumify\\Admin\\OnlineUsers', '@Forumify/admin/dashboard/components/users.html.twig')]
class OnlineUsers extends AbstractDoctrineList
{
    public function getTitle(): string
    {
        return 'admin.dashboard.online_users';
    }

    protected function getEntityClass(): string
    {
        return User::class;
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->getQuery();
    }

    protected function getQuery(): QueryBuilder
    {
        $min = (new DateTime())->sub(new DateInterval('PT5M'));
        return parent::getQuery()
            ->where('e.lastActivity > :min')
            ->setParameter('min', $min)
            ->orderBy('e.lastActivity', 'desc')
        ;
    }
}
