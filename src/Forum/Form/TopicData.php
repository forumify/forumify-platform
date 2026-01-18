<?php

declare(strict_types=1);

namespace Forumify\Forum\Form;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Forumify\Core\Entity\User;
use Forumify\Forum\Entity\ForumTag;
use Forumify\Forum\Entity\TopicTag;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class TopicData
{
    #[Assert\Length(min: 3, max: 255, normalizer: 'trim')]
    private string $title;

    #[Assert\NotBlank(allowNull: true)]
    private ?string $content = null;

    #[Assert\Image(maxSize: '10M')]
    private ?UploadedFile $image = null;

    private ?string $existingImage = null;

    private ?User $author = null;

    /**
     * @var Collection<int, ForumTag>
     */
    private Collection $tags;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getImage(): ?UploadedFile
    {
        return $this->image;
    }

    public function setImage(?UploadedFile $image): void
    {
        $this->image = $image;
    }

    public function getExistingImage(): ?string
    {
        return $this->existingImage;
    }

    public function setExistingImage(?string $existingImage): void
    {
        $this->existingImage = $existingImage;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): void
    {
        $this->author = $author;
    }

    /**
     * @return Collection<int, TopicTag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * @param Collection<int, TopicTag> $tags
     */
    public function setTags(Collection $tags): void
    {
        $this->tags = $tags;
    }
}
