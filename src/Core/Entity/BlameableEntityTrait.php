<?php

declare(strict_types=1);

namespace Forumify\Core\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

trait BlameableEntityTrait
{
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn('created_by', onDelete: 'SET NULL')]
    #[Gedmo\Blameable(on: 'create')]
    #[AuditExcludedField]
    private ?User $createdBy = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn('updated_by', onDelete: 'SET NULL')]
    #[Gedmo\Blameable(on: 'update')]
    #[AuditExcludedField]
    private ?User $updatedBy = null;

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?User $updatedBy): void
    {
        $this->updatedBy = $updatedBy;
    }
}
