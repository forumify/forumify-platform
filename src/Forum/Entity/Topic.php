<?php

declare(strict_types=1);

namespace Forumify\Forum\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\CreateProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Forumify\Core\Entity\AuditableEntityInterface;
use Forumify\Core\Entity\AuditExcludedField;
use Forumify\Core\Entity\BlameableEntityTrait;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\Core\Entity\SluggableEntityTrait;
use Forumify\Core\Entity\TimestampableEntityTrait;
use Forumify\Api\Entity\NewAsset;
use Forumify\Api\Serializer\Attribute\Asset;
use Forumify\Forum\Repository\TopicRepository;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: TopicRepository::class)]
#[ApiResource(
    routePrefix: '/forums',
    operations: [
        new Get(
            security: 'is_granted("TOPIC_VIEW", object)',
        ),
        new GetCollection(
            extraProperties: ['acl' => [
                'permission' => 'view',
                'entity' => 'forum',
            ]],
        ),
        new Post(
            uriTemplate: '/{forumId}/topics',
            uriVariables: [
                'forumId' => new Link(
                    fromClass: Forum::class,
                    toProperty: 'forum',
                    security: 'is_granted("TOPIC_CREATE", forum)',
                ),
            ],
            provider: CreateProvider::class,
        ),
        new Patch(
            security: 'is_granted("TOPIC_EDIT", object)',
        ),
        new Delete(
            security: 'is_granted("TOPIC_DELETE", object)',
        ),
        new GetCollection(
            uriTemplate: '/{forumId}/topics',
            uriVariables: [
                'forumId' => new Link(fromClass: Forum::class, toProperty: 'forum'),
            ],
            extraProperties: ['acl' => [
                'permission' => 'view',
                'entity' => 'forum',
            ]],
        ),
    ],
)]
class Topic implements SubscribableInterface, AuditableEntityInterface
{
    use IdentifiableEntityTrait;
    use BlameableEntityTrait;
    use TimestampableEntityTrait;
    use SluggableEntityTrait;

    #[ORM\Column]
    #[Groups('Topic')]
    private string $title;

    #[ORM\ManyToOne(targetEntity: Forum::class, inversedBy: 'topics')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[Groups('Topic')]
    private Forum $forum;

    /**
     * @var list<string>|null
     */
    #[ORM\Column(type: 'simple_array', nullable: true)]
    #[Groups('Topic')]
    private ?array $images = null;

    /**
     * @var list<NewAsset>|null
     */
    #[Asset('images', 'forumify.media', 'media.storage')]
    public ?array $newImages = null;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(mappedBy: 'topic', targetEntity: Comment::class, cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    private Collection $comments;

    #[ORM\ManyToOne(targetEntity: Comment::class, cascade: ['persist', 'remove'], fetch: 'EAGER')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Comment $firstComment = null;

    #[ORM\ManyToOne(targetEntity: Comment::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Comment $answer = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups('Topic')]
    private bool $locked = false;

    #[ORM\Column(type: 'boolean')]
    #[Groups('Topic')]
    private bool $pinned = false;

    #[ORM\Column(type: 'boolean')]
    #[Groups('Topic')]
    private bool $hidden = false;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    #[Groups('Topic::read')]
    #[AuditExcludedField]
    private int $views = 0;

    /**
     * @var Collection<int, ForumTag>
     */
    #[ORM\ManyToMany(targetEntity: ForumTag::class, inversedBy: 'topics')]
    #[ORM\JoinTable(
        name: 'topic_tag',
        joinColumns: new JoinColumn(onDelete: 'CASCADE'),
        inverseJoinColumns: new JoinColumn(onDelete: 'CASCADE'),
    )]
    #[Groups('Topic')]
    public Collection $tags;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->tags = new ArrayCollection();
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

    public function getForum(): Forum
    {
        return $this->forum;
    }

    public function setForum(Forum $forum): void
    {
        $this->forum = $forum;
    }

    /**
     * @return list<string>
     */
    public function getImages(): array
    {
        return $this->images ?? [];
    }

    /**
     * @param array<string>|null $images
     */
    public function setImages(?array $images): void
    {
        $this->images = empty($images) ? null : array_values($images);
    }

    public function getImage(): ?string
    {
        return $this->images[0] ?? null;
    }

    public function setImage(?string $image): void
    {
        $this->setImages($image === null ? null : [$image]);
    }

    public function getParent(): Forum
    {
        return $this->getForum();
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    /**
     * @param Collection<int, Comment>|array<Comment> $comments
     */
    public function setComments(Collection|array $comments): void
    {
        $this->comments = $comments instanceof Collection
            ? $comments
            : new ArrayCollection($comments);
    }

    public function addComment(Comment $comment): void
    {
        $this->comments->add($comment);
    }

    public function getFirstComment(): ?Comment
    {
        return $this->firstComment;
    }

    public function setFirstComment(?Comment $firstComment): void
    {
        $this->firstComment = $firstComment;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function setLocked(bool $locked): void
    {
        $this->locked = $locked;
    }

    public function isPinned(): bool
    {
        return $this->pinned;
    }

    public function setPinned(bool $pinned): void
    {
        $this->pinned = $pinned;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }

    public function getViews(): int
    {
        return $this->views;
    }

    public function setViews(int $views): void
    {
        $this->views = $views;
    }

    public function getAnswer(): ?Comment
    {
        return $this->answer;
    }

    public function setAnswer(?Comment $answer): void
    {
        $this->answer = $answer;
    }

    public function getIdentifierForAudit(): string
    {
        return (string)$this->getId();
    }

    public function getNameForAudit(): string
    {
        return $this->getTitle();
    }

    /**
     * @param array<ForumTag>|Collection<int, ForumTag> $tags
     */
    public function setTags(Collection|array $tags): void
    {
        $this->tags = $tags instanceof Collection ? $tags : new ArrayCollection($tags);
    }

    public function addTag(ForumTag $tag): void
    {
        $this->tags->add($tag);
    }

    public function removeTag(ForumTag $tag): void
    {
        $this->tags->removeElement($tag);
    }
}
