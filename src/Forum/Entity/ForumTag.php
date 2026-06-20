<?php

declare(strict_types=1);

namespace Forumify\Forum\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Entity\AuditableEntityInterface;
use Forumify\Core\Entity\BlameableEntityTrait;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\Core\Entity\SluggableEntityTrait;
use Forumify\Core\Entity\TimestampableEntityTrait;
use Forumify\Forum\Api\Provider\AvailableForumTagProvider;
use Forumify\Forum\Repository\ForumTagRepository;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ForumTagRepository::class)]
#[ApiResource(
    routePrefix: '/forums',
    operations: [
        new Get(),
        new GetCollection(),
        new Post(security: 'is_granted("forumify.admin.forums.manage")'),
        new Patch(security: 'is_granted("forumify.admin.forums.manage")'),
        new Delete(security: 'is_granted("forumify.admin.forums.manage")'),
        new GetCollection(
            '/{forumId}/available-tags',
            uriVariables: ['forumId'],
            provider: AvailableForumTagProvider::class,
            paginationEnabled: false,
        ),
    ],
)]
class ForumTag implements AuditableEntityInterface
{
    use IdentifiableEntityTrait;
    use TimestampableEntityTrait;
    use BlameableEntityTrait;
    use SluggableEntityTrait;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(allowNull: false)]
    #[Groups('ForumTag')]
    public string $title = '';

    #[ORM\Column(length: 7, options: ['fixed' => true])]
    #[Groups('ForumTag')]
    public string $color = '#ef8354';

    #[ORM\ManyToOne(targetEntity: Forum::class, inversedBy: 'tags')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[Groups('ForumTag')]
    public ?Forum $forum = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    #[Groups('ForumTag')]
    public bool $allowInSubforums = true;

    #[ORM\Column('`default`', type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups('ForumTag')]
    public bool $default = false;

    /**
     * @var Collection<int, Topic>
     */
    #[ORM\ManyToMany(targetEntity: Topic::class, mappedBy: 'tags', fetch: 'EXTRA_LAZY')]
    public Collection $topics;

    public function __construct()
    {
        $this->topics = new ArrayCollection();
    }

    public function getIdentifierForAudit(): string
    {
        return (string)$this->getId();
    }

    public function getNameForAudit(): string
    {
        return $this->title;
    }
}
