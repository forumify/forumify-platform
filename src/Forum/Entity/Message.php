<?php

declare(strict_types=1);

namespace Forumify\Forum\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Entity\BlameableEntityTrait;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\Core\Entity\TimestampableEntityTrait;
use Forumify\Forum\Api\Processor\MessagePostProcessor;
use Forumify\Forum\Form\MessageReply;
use Forumify\Forum\Repository\MessageRepository;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ApiResource(
    routePrefix: '/messenger',
    operations: [
        new Get(
            security: 'is_granted("MESSAGE_THREAD_VIEW", object.getThread())',
        ),
        new GetCollection(
            uriTemplate: '/message-threads/{threadId}/messages',
            uriVariables: [
                'threadId' => new Link(
                    fromClass: MessageThread::class,
                    toProperty: 'thread',
                    security: 'is_granted("MESSAGE_THREAD_VIEW", thread)',
                ),
            ],
        ),
        new Post(
            uriTemplate: '/message-threads/{threadId}/messages',
            uriVariables: ['threadId'],
            processor: MessagePostProcessor::class,
            input: MessageReply::class,
            read: false,
        ),
        new Patch(
            security: 'object.getCreatedBy()?.getId() == user.getUserId()',
        ),
    ],
)]
class Message
{
    use IdentifiableEntityTrait;
    use BlameableEntityTrait;
    use TimestampableEntityTrait;

    #[ORM\ManyToOne(targetEntity: MessageThread::class, inversedBy: 'messages')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[Groups('Message')]
    private MessageThread $thread;

    #[ORM\Column(type: 'text')]
    #[Groups('Message')]
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
