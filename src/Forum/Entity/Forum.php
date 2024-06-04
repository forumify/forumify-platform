<?php

declare(strict_types=1);

namespace Forumify\Forum\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Entity\AccessControlledEntityInterface;
use Forumify\Core\Entity\ACLParameters;
use Forumify\Core\Entity\HierarchicalInterface;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\Forum\Repository\ForumRepository;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: ForumRepository::class)]
class Forum implements HierarchicalInterface, AccessControlledEntityInterface
{
    public const TYPE_TEXT = 'text';
    public const TYPE_IMAGE = 'image';
    public const TYPE_MIXED = 'mixed';

    use IdentifiableEntityTrait;

    #[ORM\Column]
    private string $title = '';

    #[Gedmo\Slug(fields: ['title'])]
    #[ORM\Column(unique: true)]
    private string $slug;

    #[ORM\Column(options: ['default' => self::TYPE_TEXT])]
    private string $type = self::TYPE_TEXT;

    #[ORM\Column(type: 'text')]
    private string $content = '';

    #[ORM\ManyToOne(targetEntity: Forum::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent', onDelete: 'CASCADE')]
    private ?Forum $parent = null;

    #[ORM\Column(type: 'integer')]
    private int $position = 0;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: Forum::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $children;

    #[ORM\OneToMany(mappedBy: 'forum', targetEntity: Topic::class, cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $topics;

    #[ORM\ManyToOne(targetEntity: ForumGroup::class, inversedBy: 'forums')]
    private ?ForumGroup $group = null;

    #[ORM\OneToMany(mappedBy: 'parentForum', targetEntity: ForumGroup::class)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $groups;

    #[ORM\ManyToOne(targetEntity: Comment::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Comment $lastComment = null;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->topics = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getTitle();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): void
    {
        $this->parent = $parent;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    /**
     * @return Collection<Forum>
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

    /**
     * @return Collection<Topic>
     */
    public function getTopics(): Collection
    {
        return $this->topics;
    }

    public function setTopics(Collection|array $topics): void
    {
        $this->topics = $topics instanceof Collection
            ? $topics
            : new ArrayCollection($topics);
    }

    public function getGroup(): ?ForumGroup
    {
        return $this->group;
    }

    public function setGroup(?ForumGroup $group): void
    {
        $this->group = $group;
    }

    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function setGroups(Collection $groups): void
    {
        $this->groups = $groups;
    }

    public function getLastComment(): ?Comment
    {
        return $this->lastComment;
    }

    public function setLastComment(?Comment $lastComment): void
    {
        $this->lastComment = $lastComment;
    }

    public function getACLPermissions(): array
    {
        return ['view', 'create_topic', 'create_comment'];
    }

    public function getACLParameters(): ACLParameters
    {
        return new ACLParameters(
            self::class,
            (string)$this->getId(),
            'forumify_admin_forum',
            ['slug' => $this->getSlug()],
        );
    }
}
