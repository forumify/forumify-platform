<?php

declare(strict_types=1);

namespace Forumify\Core\Entity;

use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Repository\ReadMarkerRepository;

#[ORM\Entity(repositoryClass: ReadMarkerRepository::class)]
class ReadMarker
{
    #[ORM\Id]
    #[ORM\ManyToOne(User::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Id]
    #[ORM\Column(length: 255)]
    private string $subject;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    private int $subjectId;

    public function __construct(User $user, string $subject, int $subjectId)
    {
        $this->user = $user;
        $this->subject = $subject;
        $this->subjectId = $subjectId;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getSubjectId(): int
    {
        return $this->subjectId;
    }
}
