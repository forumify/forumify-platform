<?php

declare(strict_types=1);

namespace Forumify\Core\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Repository\AuditLogRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: AuditLogRepository::class)]
#[ORM\Index(fields: ['targetEntityClass', 'targetEntityId'])]
class AuditLog
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    public Ulid $uid;

    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    public ?User $user = null;

    #[ORM\Column(length: 255, index: true)]
    public string $action;

    /** @var class-string */
    #[ORM\Column(length: 255)]
    public ?string $targetEntityClass = null;

    #[ORM\Column(length: 255)]
    public ?string $targetEntityId = null;

    #[ORM\Column(length: 255)]
    public ?string $targetName = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    public ?array $changeset = null;
}
