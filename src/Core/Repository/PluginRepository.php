<?php

declare(strict_types=1);

namespace Forumify\Core\Repository;

use Forumify\Plugin\Entity\Plugin;

class PluginRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return Plugin::class;
    }

    public function findByActive(): array
    {
        return $this->findBy(['active' => true]);
    }
}
