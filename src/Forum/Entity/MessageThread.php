<?php

declare(strict_types=1);

namespace Forumify\Forum\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Entity\BlameableEntityTrait;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\Core\Entity\TimestampableEntityTrait;
use Forumify\Core\Entity\User;
use Forumify\Forum\Api\Processor\MessageThreadPostProcessor;
use Forumify\Forum\Form\NewMessageThread;
use Forumify\Forum\Repository\MessageThreadRepository;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MessageThreadRepository::class)]
#[ApiResource(
    routePrefix: '/messenger',
    operations: [
        new Get(
            security: 'is_granted("MESSAGE_THREAD_VIEW", object)',
        ),
        new GetCollection(),
        new Post(
            security: 'is_granted("MESSAGE_THREAD_CREATE")',
            processor: MessageThreadPostProcessor::class,
            input: NewMessageThread::class,
        ),
        new Patch(
            security: 'is_granted("MESSAGE_THREAD_VIEW", object)',
        ),
    ],
)]
class MessageThread
{
    use IdentifiableEntityTrait;
    use BlameableEntityTrait;
    use TimestampableEntityTrait;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(allowNull: false)]
    #[Groups('MessageThread')]
    private string $title = '';

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[Groups('MessageThread')]
    private Collection $participants;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(mappedBy: 'thread', targetEntity: Message::class, cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    private Collection $messages;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['default' => 'CURRENT_TIMESTAMP'])]
    #[Groups('MessageThread::read')]
    private DateTimeImmutable $lastMessageAt;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->lastMessageAt = new DateTimeImmutable();
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
     * @param Collection<int, User>|array<User> $participants
     */
    public function setParticipants(Collection|array $participants): void
    {
        $this->participants = $participants instanceof Collection ? $participants : new ArrayCollection($participants);
    }

    public function addParticipant(User $user): void
    {
        $this->participants->add($user);
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

    public function getLastMessageAt(): DateTimeImmutable
    {
        return $this->lastMessageAt;
    }

    public function addMessage(Message $message): void
    {
        $this->messages->add($message);
        $this->lastMessageAt = DateTimeImmutable::createFromMutable($message->getCreatedAt());
    }
}
