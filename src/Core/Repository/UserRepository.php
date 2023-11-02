<?php

namespace Forumify\Core\Repository;

use Forumify\Core\Entity\User;

class UserRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return User::class;
    }
}
