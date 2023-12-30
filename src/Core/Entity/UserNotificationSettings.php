<?php

declare(strict_types=1);

namespace Forumify\Core\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class UserNotificationSettings
{
    #[ORM\Id]
    #[ORM\OneToOne(inversedBy: 'notificationSettings', targetEntity: User::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: 'boolean')]
    private bool $autoSubscribeToTopics = true;

    #[ORM\Column(type: 'boolean')]
    private bool $autoSubscribeToOwnTopics = true;

    #[ORM\Column(type: 'boolean')]
    private bool $emailOnMessage = true;

    #[ORM\Column(type: 'boolean')]
    private bool $emailOnNotification = true;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function isAutoSubscribeToTopics(): bool
    {
        return $this->autoSubscribeToTopics;
    }

    public function setAutoSubscribeToTopics(bool $autoSubscribeToTopics): void
    {
        $this->autoSubscribeToTopics = $autoSubscribeToTopics;
    }

    public function isAutoSubscribeToOwnTopics(): bool
    {
        return $this->autoSubscribeToOwnTopics;
    }

    public function setAutoSubscribeToOwnTopics(bool $autoSubscribeToOwnTopics): void
    {
        $this->autoSubscribeToOwnTopics = $autoSubscribeToOwnTopics;
    }

    public function isEmailOnMessage(): bool
    {
        return $this->emailOnMessage;
    }

    public function setEmailOnMessage(bool $emailOnMessage): void
    {
        $this->emailOnMessage = $emailOnMessage;
    }

    public function isEmailOnNotification(): bool
    {
        return $this->emailOnNotification;
    }

    public function setEmailOnNotification(bool $emailOnNotification): void
    {
        $this->emailOnNotification = $emailOnNotification;
    }
}
