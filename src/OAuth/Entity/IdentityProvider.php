<?php

declare(strict_types=1);

namespace Forumify\OAuth\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Entity\AuditableEntityInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\OAuth\Repository\IdentityProviderRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: IdentityProviderRepository::class)]
class IdentityProvider implements AuditableEntityInterface
{
    use IdentifiableEntityTrait;

    #[ORM\Column(length: 128)]
    #[Assert\Length(max: 128)]
    #[Assert\NotBlank(allowNull: false)]
    private string $name;

    #[ORM\Column(length: 255, unique: true)]
    #[Gedmo\Slug(fields: ['name'], updatable: false)]
    private string $slug;

    #[ORM\Column(length: 64)]
    #[Assert\NotBlank(allowNull: false)]
    private string $type;

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $data = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * @param array<string, mixed>|null $data
     */
    public function setData(?array $data): void
    {
        $this->data = $data;
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
