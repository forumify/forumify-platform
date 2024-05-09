<?php

declare(strict_types=1);

namespace Forumify\Plugin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\Core\Repository\PluginRepository;
use Forumify\Plugin\AbstractForumifyPlugin;
use Forumify\Plugin\PluginMetadata;

#[ORM\Entity(repositoryClass: PluginRepository::class)]
class Plugin
{
    use IdentifiableEntityTrait;

    #[ORM\Column(unique: true)]
    private string $package;

    #[ORM\Column]
    private string $pluginClass;

    #[ORM\Column]
    private string $version;

    #[ORM\Column]
    private string $latestVersion;

    #[ORM\Column(type: 'boolean')]
    private bool $active = false;

    private ?AbstractForumifyPlugin $plugin = null;

    public function getPackage(): string
    {
        return $this->package;
    }

    public function setPackage(string $package): void
    {
        $this->package = $package;
    }

    public function getPluginClass(): string
    {
        return $this->pluginClass;
    }

    public function setPluginClass(string $pluginClass): void
    {
        $this->pluginClass = $pluginClass;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    public function getLatestVersion(): string
    {
        return $this->latestVersion;
    }

    public function setLatestVersion(string $latestVersion): void
    {
        $this->latestVersion = $latestVersion;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getPlugin(): ?AbstractForumifyPlugin
    {
        if ($this->plugin !== null) {
            return $this->plugin;
        }

        $class = $this->getPluginClass();
        $object = new $class();
        if ($object instanceof AbstractForumifyPlugin) {
            $this->plugin = $object;
        }

        return $this->plugin;
    }
}
