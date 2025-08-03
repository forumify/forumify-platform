<?php

declare(strict_types=1);

namespace Forumify\Forum\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Entity\BlameableEntityTrait;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\Core\Entity\TimestampableEntityTrait;
use Forumify\Core\Entity\User;
use Forumify\Forum\Provider\MessageThreadProvider;
use Forumify\Forum\Repository\MessageThreadRepository;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(provider: MessageThreadProvider::class)]
#[ORM\Entity(repositoryClass: MessageThreadRepository::class)]
class MessageThread
{
    use IdentifiableEntityTrait;
    use BlameableEntityTrait;
    use TimestampableEntityTrait;

    #[Groups('MessageThread')]
    #[ORM\Column(length: 255)]
    private string $title = '';

    /**
     * @var Collection<int, User>
     */
    #[Groups('MessageThread')]
    #[ORM\ManyToMany(targetEntity: User::class)]
    private Collection $participants;

    /**
     * @var Collection<int, Message>
     */
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

    /**
     * @return Collection<int, User>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    /**
     * @param Collection<int, User> $participants
     */
    public function setParticipants(Collection $participants): void
    {
        $this->participants = $participants;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    /**
     * @param Collection<int, Message> $messages
     */
    public function setMessages(Collection $messages): void
    {
        $this->messages = $messages;
    }
}
