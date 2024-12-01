<?php

declare(strict_types=1);

namespace Forumify\Forum\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Entity\BlameableEntityTrait;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\Core\Entity\SortableEntityInterface;
use Forumify\Core\Entity\SortableEntityTrait;
use Forumify\Core\Entity\TimestampableEntityTrait;
use Forumify\Core\Entity\User;
use Forumify\Forum\Repository\BadgeRepository;

#[ORM\Entity(BadgeRepository::class)]
class Badge implements SortableEntityInterface
{
    use IdentifiableEntityTrait;
    use BlameableEntityTrait;
    use TimestampableEntityTrait;
    use SortableEntityTrait;

    #[ORM\Column]
    private string $name;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column]
    private string $image;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'badges', fetch: 'EXTRA_LAZY')]
    private Collection $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function setImage(string $image): void
    {
        $this->image = $image;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * @param Collection<int, User> $users
     */
    public function setUsers(Collection $users): void
    {
        $this->users = $users;
    }
}
