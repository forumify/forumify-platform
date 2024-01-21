<?php

declare(strict_types=1);

namespace Forumify\Form\Entity;

use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Entity\IdentifiableEntityTrait;

#[ORM\Entity]
class FormField
{
    use IdentifiableEntityTrait;

    #[ORM\ManyToOne(targetEntity: Form::class, inversedBy: 'fields')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private Form $form;

    #[ORM\Column(type: 'json')]
    private array $options;
}
