<?php

declare(strict_types=1);

namespace Forumify\Plugin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\Core\Repository\PluginRepository;
use Forumify\Plugin\PluginInterface;
use RuntimeException;

#[ORM\Entity(repositoryClass: PluginRepository::class)]
class Plugin
{
    public const TYPE_PLUGIN = 'plugin';
    public const TYPE_THEME = 'theme';

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

    #[ORM\Column(options: ['default' => self::TYPE_PLUGIN])]
    private string $type;

    #[ORM\Column(nullable: true)]
    private ?string $subscriptionVersion = null;

    private ?PluginInterface $plugin = null;

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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getSubscriptionVersion(): ?string
    {
        return $this->subscriptionVersion;
    }

    public function setSubscriptionVersion(?string $subscriptionVersion): void
    {
        $this->subscriptionVersion = $subscriptionVersion;
    }

    public function getPlugin(): PluginInterface
    {
        if ($this->plugin !== null) {
            return $this->plugin;
        }

        $class = $this->getPluginClass();
        $object = new $class();
        if (!$object instanceof PluginInterface) {
            throw new RuntimeException('Cannot use ' . get_class($this) . ' as a plugin. Did you forget to implement PluginInterface?');
        }

        $this->plugin = $object;
        return $this->plugin;
    }
}
