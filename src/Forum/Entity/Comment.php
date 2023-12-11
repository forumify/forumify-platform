<?php

declare(strict_types=1);

namespace Forumify\Forum\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Entity\BlameableEntityTrait;
use Forumify\Core\Entity\HierarchicalInterface;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\Core\Entity\TimestampableEntityTrait;

#[ORM\Entity]
class Comment implements SubscribableInterface
{
    use IdentifiableEntityTrait;
    use BlameableEntityTrait;
    use TimestampableEntityTrait;

    #[ORM\Column(type: 'text')]
    private string $content;

    #[ORM\ManyToOne(targetEntity: Topic::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private Topic $topic;

    #[ORM\OneToMany(mappedBy: 'comment', targetEntity: CommentReaction::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $reactions;

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getTopic(): Topic
    {
        return $this->topic;
    }

    public function setTopic(Topic $topic): void
    {
        $this->topic = $topic;
    }

    public function getReactions(): Collection
    {
        return $this->reactions;
    }

    public function setReactions(Collection $reactions): void
    {
        $this->reactions = $reactions;
    }

    public function getParent(): ?HierarchicalInterface
    {
        return $this->getTopic();
    }
}
