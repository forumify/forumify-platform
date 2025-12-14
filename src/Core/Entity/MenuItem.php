<?php

declare(strict_types=1);

namespace Forumify\Core\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Repository\MenuItemRepository;

#[ORM\Entity(repositoryClass: MenuItemRepository::class)]
class MenuItem implements AccessControlledEntityInterface, SortableEntityInterface
{
    use IdentifiableEntityTrait;
    use SortableEntityTrait;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255)]
    private string $type;

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')]
    private array $payload = [];

    #[ORM\ManyToOne(targetEntity: MenuItem::class, inversedBy: 'children')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?MenuItem $parent = null;

    /**
     * @var Collection<int, MenuItem>
     */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: MenuItem::class, cascade: ['persist'])]
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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getPayloadValue(string $key): mixed
    {
        return $this->payload[$key] ?? null;
    }

    /**
     * @param array<string, mixed> $payload
     */
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
     * @return Collection<int, MenuItem>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * @param Collection<int, MenuItem>|array<int, MenuItem> $children
     */
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
        $parentId = $this->getParent()?->getId();

        return new ACLParameters(
            self::class,
            (string)$this->getId(),
            'forumify_admin_menu_builder',
            $parentId !== null ? ['id' => $parentId] : []
        );
    }
}
