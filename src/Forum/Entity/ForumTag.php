<?php

declare(strict_types=1);

namespace Forumify\Forum\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Entity\BlameableEntityTrait;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\Core\Entity\SluggableEntityTrait;
use Forumify\Core\Entity\TimestampableEntityTrait;
use Forumify\Forum\Repository\ForumTagRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ForumTagRepository::class)]
class ForumTag
{
    use IdentifiableEntityTrait;
    use TimestampableEntityTrait;
    use BlameableEntityTrait;
    use SluggableEntityTrait;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(allowNull: false)]
    public string $title = '';

    #[ORM\Column(length: 7, options: ['fixed' => true])]
    public string $color = '#ef8354';

    #[ORM\ManyToOne(targetEntity: Forum::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    public ?Forum $forum = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    public bool $allowInSubforums = true;

    #[ORM\Column('`default`', type: Types::BOOLEAN, options: ['default' => false])]
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
}
