<?php

declare(strict_types=1);

namespace Forumify\Core\Repository;

use Forumify\Core\Entity\Theme;

/**
 * @extends AbstractRepository<Theme>
 */
class ThemeRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return Theme::class;
    }

    public function findActiveTheme(): ?Theme
    {
        return $this->findOneBy(['active' => true]);
    }
}
