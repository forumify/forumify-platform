<?php

declare(strict_types=1);

namespace Forumify\Core\Repository;

use Forumify\Plugin\Entity\Plugin;

/**
 * @extends AbstractRepository<Plugin>
 */
class PluginRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return Plugin::class;
    }

    /**
     * @return array<Plugin>
     */
    public function findByActive(): array
    {
        return $this->findBy(['active' => true]);
    }

    /**
     * @return array<Plugin>
     */
    public function findActivePlugins(): array
    {
        return $this->findBy(['type' => Plugin::TYPE_PLUGIN, 'active' => true]);
    }
}
