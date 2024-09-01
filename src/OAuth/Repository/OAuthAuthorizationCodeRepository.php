<?php

declare(strict_types=1);

namespace Forumify\OAuth\Repository;

use Forumify\Core\Repository\AbstractRepository;
use Forumify\OAuth\Entity\OAuthAuthorizationCode;

class OAuthAuthorizationCodeRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return OAuthAuthorizationCode::class;
    }
}
