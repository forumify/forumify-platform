<?php

declare(strict_types=1);

namespace Forumify\OAuth\Repository;

use Forumify\Core\Repository\AbstractRepository;
use Forumify\OAuth\Entity\OAuthClient;

class OAuthClientRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return OAuthClient::class;
    }
}
