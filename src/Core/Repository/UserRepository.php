<?php

namespace Forumify\Core\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Forumify\Core\Entity\User;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

class UserRepository extends AbstractRepository implements UserLoaderInterface
{
    private $settingRepository;

    public function __construct(ManagerRegistry $registry, SettingRepository $settingRepository)
    {
        parent::__construct($registry);
        $this->settingRepository = $settingRepository;
    }

    public static function getEntityClass(): string
    {
        return User::class;
    }

    public function loadUserByIdentifier(string $usernameOrEmail): ?User
    {
        $queryBuilder = $this->createQueryBuilder('u');

        $where = match($this->settingRepository->get('core.enable_email_login')) {
            'email' => 'u.email = :query',
            'both' => 'u.username = :query OR u.email = :query',
            default => 'u.username = :query',
        };
        $queryBuilder->where($where);

        return $this->createQueryBuilder('u')
            ->where($where)
            ->setParameter('query', $usernameOrEmail)
            ->getQuery()
            ->getOneOrNullResult();

    }
}