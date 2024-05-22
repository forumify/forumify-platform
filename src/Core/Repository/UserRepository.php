<?php

namespace Forumify\Core\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Forumify\Core\Entity\User;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

class UserRepository extends AbstractRepository implements UserLoaderInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly SettingRepository $settingRepository
    ) {
        parent::__construct($registry);
    }

    public static function getEntityClass(): string
    {
        return User::class;
    }

    public function loadUserByIdentifier(string $identifier): ?User
    {
        $where = match ($this->settingRepository->get('forumify.login_method')) {
            'email' => 'u.email = :query',
            'both' => 'u.username = :query OR u.email = :query',
            default => 'u.username = :query',
        };

        return $this->createQueryBuilder('u')
            ->where($where)
            ->setParameter('query', $identifier)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
