<?php

declare(strict_types=1);

namespace Forumify\Core\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

trait TimestampableEntityTrait
{
    #[ORM\Column(type: 'datetime', nullable: true, index: true)]
    #[Gedmo\Timestampable(on: 'create')]
    #[AuditExcludedField]
    private DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true, index: true)]
    #[Gedmo\Timestampable(on: 'update')]
    #[AuditExcludedField]
    private ?DateTime $updatedAt = null;

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
