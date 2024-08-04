<?php

declare(strict_types=1);

namespace Forumify\Api\Repository;

use Forumify\Api\Entity\OAuthClient;
use Forumify\Core\Repository\AbstractRepository;

class OAuthClientRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return OAuthClient::class;
    }
}
