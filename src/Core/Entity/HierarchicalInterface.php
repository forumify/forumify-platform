<?php
declare(strict_types=1);

namespace Forumify\Core\Entity;

interface HierarchicalInterface
{
    public function getId(): int;
    public function getParent(): ?HierarchicalInterface;
}
