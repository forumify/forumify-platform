<?php

declare(strict_types=1);

namespace Forumify\Core\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;

interface AuthorizableInterface extends UserInterface
{
    public function getId(): int;

    /**
     * @return Collection<int, Role>
     */
    public function getRoleEntities(): Collection;

    public function getLastActivity(): ?DateTime;

    public function setLastActivity(DateTime $lastActivity): void;
}
