<?php

declare(strict_types=1);

namespace Forumify\Core\Entity;

use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Repository\SettingRepository;

#[ORM\Entity(repositoryClass: SettingRepository::class)]
class Setting
{
    #[ORM\Id]
    #[ORM\Column('`key`')]
    private readonly string $key;

    #[ORM\Column(type: 'text')]
    private string $value = '';

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }
}
