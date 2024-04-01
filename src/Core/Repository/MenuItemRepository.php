<?php

declare(strict_types=1);

namespace Forumify\Core\Repository;

use Forumify\Core\Entity\MenuItem;

class MenuItemRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return MenuItem::class;
    }

    /**
     * @return array<MenuItem>
     */
    public function getRoots(): array
    {
        return $this->findBy(['parent' => null], ['position' => 'ASC']);
    }
}
