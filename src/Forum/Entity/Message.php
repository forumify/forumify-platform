<?php

declare(strict_types=1);

namespace Forumify\Forum\Entity;

use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Entity\BlameableEntityTrait;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\Core\Entity\TimestampableEntityTrait;
use Forumify\Forum\Repository\MessageRepository;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    use IdentifiableEntityTrait;
    use BlameableEntityTrait;
    use TimestampableEntityTrait;

    #[ORM\ManyToOne(targetEntity: MessageThread::class, inversedBy: 'messages')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private MessageThread $thread;

    #[ORM\Column(type: 'text')]
    private string $content;

    public function getThread(): MessageThread
    {
        return $this->thread;
    }

    public function setThread(MessageThread $thread): void
    {
        $this->thread = $thread;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }
}
