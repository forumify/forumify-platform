<?php

declare(strict_types=1);

namespace Forumify\OAuth\Repository;

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
}
