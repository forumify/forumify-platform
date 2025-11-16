<?php

declare(strict_types=1);

namespace Forumify\Core\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Notification
{
    use IdentifiableEntityTrait;
    use TimestampableEntityTrait;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private User $recipient;

    #[ORM\Column]
    private string $type;

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')]
    private array $context;

    #[ORM\Column(type: 'boolean')]
    private bool $seen = false;

    /** @var array<string, mixed>|null */
    private ?array $deserializedContext = null;

    /**
     * @param array<string, mixed> $context
     */
    public function __construct(string $type, User $recipient, array $context = [])
    {
        $this->type = $type;
        $this->recipient = $recipient;
        $this->context = $context;
    }

    public function getRecipient(): User
    {
        return $this->recipient;
    }

    public function setRecipient(User $recipient): void
    {
        $this->recipient = $recipient;
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
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function setContext(array $context): void
    {
        $this->context = $context;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getDeserializedContext(): ?array
    {
        return $this->deserializedContext;
    }

    /**
     * @param array<string, mixed> $deserializedContext
     */
    public function setDeserializedContext(array $deserializedContext): void
    {
        $this->deserializedContext = $deserializedContext;
    }

    public function isSeen(): bool
    {
        return $this->seen;
    }

    public function setSeen(bool $seen): void
    {
        $this->seen = $seen;
    }
}
