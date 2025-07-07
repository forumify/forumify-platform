<?php

declare(strict_types=1);

namespace Forumify\Forum\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Entity\AccessControlledEntityInterface;
use Forumify\Core\Entity\ACLParameters;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\Core\Entity\SortableEntityInterface;
use Forumify\Core\Entity\SortableEntityTrait;
use Forumify\Forum\Repository\ForumGroupRepository;

#[ORM\Entity(repositoryClass: ForumGroupRepository::class)]
class ForumGroup implements AccessControlledEntityInterface, SortableEntityInterface
{
    use IdentifiableEntityTrait;
    use SortableEntityTrait;

    #[ORM\Column(length: 255)]
    private string $title;

    /**
     * @var Collection<int, Forum>
     */
    #[ORM\OneToMany(mappedBy: 'group', targetEntity: Forum::class)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $forums;

    #[ORM\ManyToOne(targetEntity: Forum::class, inversedBy: 'groups')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Forum $parentForum = null;

    public function __construct()
    {
        $this->forums = new ArrayCollection();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return Collection<int, Forum>
     */
    public function getForums(): Collection
    {
        return $this->forums;
    }

    /**
     * @param Collection<int, Forum> $forums
     */
    public function setForums(Collection $forums): void
    {
        $this->forums = $forums;
    }

    public function getParentForum(): ?Forum
    {
        return $this->parentForum;
    }

    public function setParentForum(?Forum $parentForum): void
    {
        $this->parentForum = $parentForum;
    }

    public function getACLPermissions(): array
    {
        return ['view'];
    }

    public function getACLParameters(): ACLParameters
    {
        $returnParameters = $this->getParentForum() === null
            ? []
            : ['slug' => $this->getParentForum()->getSlug()];

        return new ACLParameters(
            self::class,
            (string)$this->getId(),
            'forumify_admin_forum',
            $returnParameters,
        );
    }
}
