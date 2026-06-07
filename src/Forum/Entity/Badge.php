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
use Doctrine\ORM\Mapping as ORM;
use Forumify\Api\Entity\NewAsset;
use Forumify\Api\Serializer\Attribute\Asset;
use Forumify\Core\Entity\AuditableEntityInterface;
use Forumify\Core\Entity\BlameableEntityTrait;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\Core\Entity\SortableEntityInterface;
use Forumify\Core\Entity\SortableEntityTrait;
use Forumify\Core\Entity\TimestampableEntityTrait;
use Forumify\Core\Entity\User;
use Forumify\Forum\Repository\BadgeRepository;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(BadgeRepository::class)]
#[ApiResource(
    security: 'is_granted("forumify.admin.settings.badges.view")',
    routePrefix: '/forums',
    operations: [
        new Get(),
        new GetCollection(),
        new Post(security: 'is_granted("forumify.admin.settings.badges.manage")'),
        new Patch(security: 'is_granted("forumify.admin.settings.badges.manage")'),
        new Delete(security: 'is_granted("forumify.admin.settings.badges.manage")'),
    ],
)]
class Badge implements SortableEntityInterface, AuditableEntityInterface
{
    use IdentifiableEntityTrait;
    use BlameableEntityTrait;
    use TimestampableEntityTrait;
    use SortableEntityTrait;

    #[ORM\Column(length: 255)]
    #[Groups('Badge')]
    private string $name;

    #[ORM\Column(type: 'text')]
    #[Groups('Badge')]
    private string $description;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(allowNull: false)]
    #[Groups('Badge')]
    private string $image;

    #[Asset('image', 'forumify.asset', 'asset.storage')]
    public ?NewAsset $newImage = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'badges', fetch: 'EXTRA_LAZY')]
    private Collection $users;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $showOnForum = false;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function setImage(string $image): void
    {
        $this->image = $image;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * @param Collection<int, User> $users
     */
    public function setUsers(Collection $users): void
    {
        $this->users = $users;
    }

    public function isShowOnForum(): bool
    {
        return $this->showOnForum;
    }

    public function setShowOnForum(bool $showOnForum): void
    {
        $this->showOnForum = $showOnForum;
    }

    public function getIdentifierForAudit(): string
    {
        return (string)$this->getId();
    }

    public function getNameForAudit(): string
    {
        return $this->getName();
    }
}
