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

    #[ORM\Column(type: 'json')]
    private array $context;

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

    public function getContext(): array
    {
        return $this->context;
    }

    public function setContext(array $context): void
    {
        $this->context = $context;
    }
}
