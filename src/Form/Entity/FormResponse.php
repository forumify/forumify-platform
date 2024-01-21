<?php

declare(strict_types=1);

namespace Forumify\Form\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Entity\BlameableEntityTrait;
use Forumify\Core\Entity\IdentifiableEntityTrait;

#[ORM\Entity]
class FormResponse
{
    use IdentifiableEntityTrait;
    use BlameableEntityTrait;

    #[ORM\ManyToOne(targetEntity: Form::class, inversedBy: 'responses')]
    private Form $form;

    /**
     * @var Collection<FormResponseValue>
     */
    #[ORM\OneToMany(mappedBy: 'response', targetEntity: FormResponseValue::class)]
    private Collection $values;

    public function __construct()
    {
        $this->values = new ArrayCollection();
    }
}
