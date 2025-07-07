<?php

declare(strict_types=1);

namespace Forumify\Forum\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Entity\BlameableEntityTrait;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\Core\Entity\SluggableEntityTrait;
use Forumify\Core\Entity\TimestampableEntityTrait;
use Forumify\Forum\Repository\TopicRepository;

#[ORM\Entity(repositoryClass: TopicRepository::class)]
class Topic implements SubscribableInterface
{
    use IdentifiableEntityTrait;
    use BlameableEntityTrait;
    use TimestampableEntityTrait;
    use SluggableEntityTrait;

    #[ORM\Column]
    private string $title;

    #[ORM\ManyToOne(targetEntity: Forum::class, inversedBy: 'topics')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private Forum $forum;

    /**
     * @var Collection<int, TopicImage>
     */
    #[ORM\OneToMany(mappedBy: 'topic', targetEntity: TopicImage::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    private Collection $images;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(mappedBy: 'topic', targetEntity: Comment::class, cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    private Collection $comments;

    #[ORM\ManyToOne(targetEntity: Comment::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Comment $firstComment = null;

    #[ORM\ManyToOne(targetEntity: Comment::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Comment $answer = null;

    #[ORM\Column(type: 'boolean')]
    private bool $locked = false;

    #[ORM\Column(type: 'boolean')]
    private bool $pinned = false;

    #[ORM\Column(type: 'boolean')]
    private bool $hidden = false;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    private int $views = 0;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->comments = new ArrayCollection();
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
     * @return Collection<int, TopicImage>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    /**
     * @param Collection<int, TopicImage> $images
     */
    public function setImages(Collection $images): void
    {
        $this->images = $images;
    }

    public function getImage(): ?string
    {
        $first = $this->images->first();
        return $first ? $first->getImage() : null;
    }

    public function setImage(?string $image): void
    {
        $first = $this->images->first();
        if (!$first) {
            $first = new TopicImage();
            $first->setTopic($this);
            $this->getImages()->add($first);
        }
        $first->setImage($image);
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
}
