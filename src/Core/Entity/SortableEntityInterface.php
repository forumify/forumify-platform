<?php
declare(strict_types=1);

namespace Forumify\Core\Entity;

interface SortableEntityInterface
{
    public function getPosition(): int;

    /**
     * @param int $position
     * @return void
     */
    public function setPosition(int $position): void;
}
