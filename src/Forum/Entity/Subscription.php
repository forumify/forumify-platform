<?php

declare(strict_types=1);

namespace Forumify\Forum\Entity;

use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\Core\Entity\User;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'subscription_uniq', fields: ['user', 'type', 'subjectId'])]
class Subscription
{
    use IdentifiableEntityTrait;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'subscriptions')]
    #[ORM\JoinColumn('user', onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: 'string')]
    private string $type;

    #[ORM\Column(type: 'integer')]
    private int $subjectId;

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getSubjectId(): int
    {
        return $this->subjectId;
    }

    public function setSubjectId(int $subjectId): void
    {
        $this->subjectId = $subjectId;
    }
}
