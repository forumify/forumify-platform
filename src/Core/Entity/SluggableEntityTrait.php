<?php

declare(strict_types=1);

namespace Forumify\Core\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

trait SluggableEntityTrait
{
    #[ORM\Column(length: 255, unique: true)]
    #[Gedmo\Slug(fields: ['title'], updatable: false)]
    private string $slug;

    public function getSlug(): string
    {
        return $this->slug;
    }
}
