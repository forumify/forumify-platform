<?php

declare(strict_types=1);

namespace Forumify\Forum\Form;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Forumify\Core\Entity\User;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class NewMessageThread
{
    #[Assert\NotBlank]
    #[Groups('MessageThread::write')]
    private string $title;

    /**
     * @var ArrayCollection<int, User>
     */
    #[Assert\Count(min: 1)]
    #[Groups('MessageThread::write')]
    private ArrayCollection $participants;

    #[Assert\NotBlank]
    #[Groups('MessageThread::write')]
    private string $message;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return ArrayCollection<int, User>
     */
    public function getParticipants(): ArrayCollection
    {
        return $this->participants;
    }

    /**
     * @param ArrayCollection<int, User>|array<User> $participants
     */
    public function setParticipants(ArrayCollection|array $participants): void
    {
        $this->participants = $participants instanceof Collection ? $participants : new ArrayCollection($participants);
    }
}
