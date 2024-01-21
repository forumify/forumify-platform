<?php

declare(strict_types=1);

namespace Forumify\Form\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\Core\Entity\SluggableEntityTrait;

#[ORM\Entity]
class Form
{
    use IdentifiableEntityTrait;
    use SluggableEntityTrait;

    #[ORM\Column]
    private string $title;

    /**
     * @var Collection<FormField>
     */
    #[ORM\OneToMany(mappedBy: 'form', targetEntity: FormField::class)]
    private Collection $fields;

    /**
     * @var Collection<FormResponse>
     */
    #[ORM\OneToMany(mappedBy: 'form', targetEntity: FormResponse::class)]
    private Collection $responses;

    public function __construct()
    {
        $this->fields = new ArrayCollection();
        $this->responses = new ArrayCollection();
    }
}
