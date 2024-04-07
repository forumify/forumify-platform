<?php

declare(strict_types=1);

namespace Forumify\Core\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Repository\MenuItemRepository;

#[ORM\Entity(repositoryClass: MenuItemRepository::class)]
class MenuItem implements AccessControlledEntityInterface
{
    use IdentifiableEntityTrait;

    #[ORM\Column]
    private string $name;

    #[ORM\Column(type: 'integer')]
    private int $position = 0;

    #[ORM\Column]
    private string $type;

    #[ORM\Column(type: 'json')]
    private array $payload = [];

    #[ORM\ManyToOne(MenuItem::class, inversedBy: 'children')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?MenuItem $parent = null;

    /**
     * @var Collection<MenuItem>
     */
    #[ORM\OneToMany('parent', MenuItem::class, cascade: ['persist'])]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getPayloadValue(string $key): mixed
    {
        return $this->payload[$key] ?? null;
    }

    public function setPayload(array $payload): void
    {
        $this->payload = $payload;
    }

    public function getParent(): ?MenuItem
    {
        return $this->parent;
    }

    public function setParent(?MenuItem $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return Collection<MenuItem>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function setChildren(Collection|array $children): void
    {
        $this->children = $children instanceof Collection
            ? $children
            : new ArrayCollection($children);
    }

    public function getACLPermissions(): array
    {
        return ['view'];
    }

    public function getACLParameters(): ACLParameters
    {
        $parentId =  $this->getParent()?->getId();

        return new ACLParameters(
            self::class,
            (string)$this->getId(),
            'forumify_admin_menu_builder',
            $parentId !== null ? ['id' => $parentId] : []
        );
    }
}
