<?php

declare(strict_types=1);

namespace Forumify\OAuth\Repository;

use Forumify\Core\Entity\User;
use Forumify\Core\Repository\AbstractRepository;
use Forumify\OAuth\Entity\IdentityProviderUser;

/**
 * @extends AbstractRepository<IdentityProviderUser>
 */
class IdentityProviderUserRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return IdentityProviderUser::class;
    }

    /**
     * @return array<IdentityProviderUser>
     */
    public function findByUserAndIdpType(User $user, string $idpType): array
    {
        return $this
            ->createQueryBuilder('ipu')
            ->innerJoin('ipu.identityProvider', 'ip')
            ->where('ip.type = :type')
            ->andWhere('ipu.user = :user')
            ->setParameter('type', $idpType)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }
}
