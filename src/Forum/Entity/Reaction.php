<?php

declare(strict_types=1);

namespace Forumify\Forum\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Forumify\Api\Entity\NewAsset;
use Forumify\Api\Serializer\Attribute\Asset;
use Forumify\Core\Entity\AuditableEntityInterface;
use Forumify\Core\Entity\BlameableEntityTrait;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\Core\Entity\TimestampableEntityTrait;
use Forumify\Forum\Repository\ReactionRepository;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ReactionRepository::class)]
#[ApiResource(
    security: 'is_granted("forumify.admin.settings.reactions.view")',
    routePrefix: '/forums',
    operations: [
        new Get(),
        new GetCollection(),
        new Post(security: 'is_granted("forumify.admin.settings.reactions.manage")'),
        new Patch(security: 'is_granted("forumify.admin.settings.reactions.manage")'),
        new Delete(security: 'is_granted("forumify.admin.settings.reactions.manage")'),
    ],
)]
class Reaction implements AuditableEntityInterface
{
    use IdentifiableEntityTrait;
    use BlameableEntityTrait;
    use TimestampableEntityTrait;

    #[ORM\Column(length: 255)]
    #[Groups('Reaction')]
    public string $name;

    #[ORM\Column(length: 255)]
    #[Groups('Reaction')]
    public string $image;

    #[Asset('image', 'forumify.asset', 'asset.storage')]
    public ?NewAsset $newImage = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups('Reaction')]
    public int $reputation = 0;

    public function getIdentifierForAudit(): string
    {
        return (string)$this->getId();
    }

    public function getNameForAudit(): string
    {
        return $this->name;
    }
}
