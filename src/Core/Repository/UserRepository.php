<?php

declare(strict_types=1);

namespace Forumify\Core\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Forumify\Core\Entity\User;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @extends AbstractRepository<User>
 */
class UserRepository extends AbstractRepository implements UserLoaderInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly SettingRepository $settingRepository,
        private readonly RequestStack $requestStack,
    ) {
        parent::__construct($registry);
    }

    public static function getEntityClass(): string
    {
        return User::class;
    }

    public function loadUserByIdentifier(string $identifier): ?User
    {
        return $this->createQueryBuilder('u')
            ->where($this->getUserIdentifierWhere())
            ->setParameter('query', $identifier)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function getUserIdentifierWhere(): string
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request?->cookies->has('REMEMBERME')) {
            return 'u.username = :query';
        }

        return match ($this->settingRepository->get('forumify.login_method')) {
            'email' => 'u.email = :query',
            'both' => 'u.username = :query OR u.email = :query',
            default => 'u.username = :query',
        };
    }
}
