<?php

declare(strict_types=1);

namespace Forumify\OAuth\Repository;

use Forumify\Core\Repository\AbstractRepository;
use Forumify\OAuth\Entity\IdentityProvider;

/**
 * @extends AbstractRepository<IdentityProvider>
 */
class IdentityProviderRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return IdentityProvider::class;
    }
}
