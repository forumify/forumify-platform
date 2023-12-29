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

#[Gedmo\Tree(type: 'nested')]
#[ORM\Entity(repositoryClass: ForumRepository::class)]
class Forum implements HierarchicalInterface, AccessControlledEntityInterface
{
    use IdentifiableEntityTrait;

    #[ORM\Column]
    private string $title = '';

    #[Gedmo\Slug(fields: ['title'])]
    #[ORM\Column(unique: true)]
    private string $slug;

    #[ORM\Column(type: 'text')]
    private string $content = '';

    #[Gedmo\TreeLeft]
    #[ORM\Column(name: 'lft', type: 'integer')]
    private int $lft;

    #[Gedmo\TreeLevel]
    #[ORM\Column(name: 'lvl', type: 'integer')]
    private int $lvl;

    #[Gedmo\TreeRight]
    #[ORM\Column(name: 'rgt', type: 'integer')]
    private int $rgt;

    #[Gedmo\TreeRoot]
    #[ORM\ManyToOne(targetEntity: Forum::class)]
    #[ORM\JoinColumn(name: 'root', onDelete: 'CASCADE')]
    private ?Forum $root = null;

    #[Gedmo\TreeParent]
    #[ORM\ManyToOne(targetEntity: Forum::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent', onDelete: 'CASCADE')]
    private ?Forum $parent = null;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: Forum::class, cascade: ['remove'])]
    #[ORM\OrderBy(['lft' => 'ASC'])]
    private Collection $children;

    #[ORM\OneToMany(mappedBy: 'forum', targetEntity: Topic::class, cascade: ['remove'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    private Collection $topics;

    #[ORM\ManyToOne(targetEntity: ForumGroup::class, inversedBy: 'forums')]
    private ?ForumGroup $group = null;

    #[ORM\OneToMany(mappedBy: 'parentForum', targetEntity: ForumGroup::class)]
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

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getRoot(): ?self
    {
        return $this->root;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): void
    {
        $this->parent = $parent;
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
