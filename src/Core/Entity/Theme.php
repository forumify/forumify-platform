<?php

declare(strict_types=1);

namespace Forumify\Core\Entity;

use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Repository\ThemeRepository;
use Forumify\Plugin\Entity\Plugin;

#[ORM\Entity(repositoryClass: ThemeRepository::class)]
class Theme
{
    use IdentifiableEntityTrait;
    use TimestampableEntityTrait;

    #[ORM\Column]
    private string $name;

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')]
    private array $themeConfig = [];

    #[ORM\Column(type: 'text')]
    private string $css = '';

    #[ORM\Column(type: 'boolean')]
    private bool $active = false;

    #[ORM\OneToOne(targetEntity: Plugin::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private Plugin $plugin;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return array<string, mixed>
     */
    public function getThemeConfig(): array
    {
        return $this->themeConfig;
    }

    /**
     * @param array<string, mixed> $themeConfig
     */
    public function setThemeConfig(array $themeConfig): void
    {
        $this->themeConfig = $themeConfig;
    }

    public function getCss(): string
    {
        return $this->css;
    }

    public function setCss(string $css): void
    {
        $this->css = $css;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }

    public function setPlugin(Plugin $plugin): void
    {
        $this->plugin = $plugin;
    }
}
