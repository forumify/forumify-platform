<?php

declare(strict_types=1);

namespace Forumify\Forum\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\CreateProvider;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Entity\BlameableEntityTrait;
use Forumify\Core\Entity\HierarchicalInterface;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\Core\Entity\TimestampableEntityTrait;
use Forumify\Forum\Repository\CommentRepository;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ApiResource(
    routePrefix: '/forums',
    operations: [
        new Get(
            security: 'is_granted("COMMENT_VIEW", object)',
        ),
        new GetCollection(
            extraProperties: ['acl' => [
                'permission' => 'view',
                'entity' => 'topic.forum',
            ]],
        ),
        new Patch(
            security: 'is_granted("COMMENT_EDIT", object)',
        ),
        new Delete(
            security: 'is_granted("COMMENT_DELETE", object)',
        ),
        new GetCollection(
            uriTemplate: '/topics/{topicId}/comments',
            uriVariables: [
                'topicId' => new Link(
                    fromClass: Topic::class,
                    toProperty: 'topic',
                    security: 'is_granted("TOPIC_VIEW", topic)'
                ),
            ],
        ),
        new Post(
            uriTemplate: '/topics/{topicId}/comments',
            uriVariables: [
                'topicId' => new Link(
                    fromClass: Topic::class,
                    toProperty: 'topic',
                    security: 'is_granted("COMMENT_CREATE", topic)'
                ),
            ],
            provider: CreateProvider::class,
        ),
    ],
)]
class Comment implements SubscribableInterface
{
    use IdentifiableEntityTrait;
    use BlameableEntityTrait;
    use TimestampableEntityTrait;

    #[ORM\Column(type: 'text')]
    #[Groups('Comment')]
    private string $content;

    #[ORM\ManyToOne(targetEntity: Topic::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[Groups('Comment')]
    private Topic $topic;

    /**
     * @var Collection<int, Reaction>
     */
    #[ORM\OneToMany(mappedBy: 'comment', targetEntity: CommentReaction::class, cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    private Collection $reactions;

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getTopic(): Topic
    {
        return $this->topic;
    }

    public function setTopic(Topic $topic): void
    {
        $this->topic = $topic;
    }

    /**
     * @return Collection<int, Reaction>
     */
    public function getReactions(): Collection
    {
        return $this->reactions;
    }

    /**
     * @param Collection<int, Reaction> $reactions
     */
    public function setReactions(Collection $reactions): void
    {
        $this->reactions = $reactions;
    }

    public function getParent(): ?HierarchicalInterface
    {
        return $this->getTopic();
    }
}
