<?php
declare(strict_types=1);

namespace Forumify\Core\Entity;

use Doctrine\ORM\Mapping as ORM;

trait IdentifiableEntityTrait
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
