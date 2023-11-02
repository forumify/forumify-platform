<?php

declare(strict_types=1);

namespace Forumify\Forum\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Entity\BlameableEntityTrait;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\Core\Entity\TimestampableEntityTrait;
use Forumify\Core\Entity\User;

#[ORM\Entity]
class MessageThread
{
    use IdentifiableEntityTrait;
    use BlameableEntityTrait;
    use TimestampableEntityTrait;

    #[ORM\Column]
    private string $title;

    #[ORM\ManyToMany(targetEntity: User::class)]
    private Collection $participants;

    #[ORM\OneToMany(mappedBy: 'thread', targetEntity: Message::class, cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    private Collection $messages;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function setParticipants(Collection $participants): void
    {
        $this->participants = $participants;
    }

    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function setMessages(Collection $messages): void
    {
        $this->messages = $messages;
    }
}
