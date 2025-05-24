<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use Forumify\Plugin\Entity\Plugin;

class TemplateNamespace
{
    public function __construct(
        private readonly string $name,
        private readonly string $root,
        private readonly string $overrideRoot
    ) {
    }

    public static function fromPlugin(string $root, Plugin $plugin): self
    {
        $class = $plugin->getPluginClass();
        $namespace = substr($class, strrpos($class, '\\') + 1);

        return new self(
            $namespace,
            "{$root}/vendor/{$plugin->getPackage()}/templates",
            "{$root}/vendor/{$plugin->getPackage()}/templates/bundles",
        );
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRoot(): string
    {
        return $this->root;
    }

    public function getOverrideRoot(): string
    {
        return $this->overrideRoot;
    }
}
