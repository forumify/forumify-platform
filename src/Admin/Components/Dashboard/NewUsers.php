<?php

declare(strict_types=1);

namespace Forumify\Admin\Components\Dashboard;

use DateInterval;
use DateTime;
use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Component\List\AbstractDoctrineList;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\UserRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('Forumify\\Admin\\NewUsers', '@Forumify/admin/dashboard/components/users.html.twig')]
class NewUsers extends AbstractDoctrineList
{
    public function __construct(private readonly UserRepository $userRepository)
    {
        $this->limit = 8;
    }

    public function getTitle(): string
    {
        return 'admin.dashboard.new_users';
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
        $min = (new DateTime())->sub(new DateInterval('P1M'));
        return parent::getQuery()
            ->where('e.createdAt > :min')
            ->setParameter('min', $min)
            ->orderBy('e.createdAt', 'desc')
        ;
    }
}
