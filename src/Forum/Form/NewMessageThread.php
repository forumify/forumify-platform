<?php

declare(strict_types=1);

namespace Forumify\Forum\Form;

use Doctrine\Common\Collections\ArrayCollection;
use Forumify\Core\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

class NewMessageThread
{
    #[Assert\NotBlank]
    private string $title;

    /**
     * @var ArrayCollection<int, User>
     */
    #[Assert\Count(min: 1)]
    private ArrayCollection $participants;

    #[Assert\NotBlank]
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
     * @param ArrayCollection<int, User> $participants
     */
    public function setParticipants(ArrayCollection $participants): void
    {
        $this->participants = $participants;
    }
}
