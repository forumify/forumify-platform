<?php

declare(strict_types=1);

namespace Forumify\Form\Entity;

use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Entity\IdentifiableEntityTrait;

#[ORM\Entity]
class FormResponseValue
{
    use IdentifiableEntityTrait;

    #[ORM\Column(type: 'text')]
    private string $value;

    #[ORM\ManyToOne(targetEntity: FormResponse::class, inversedBy: 'values')]
    private FormResponse $response;
}
