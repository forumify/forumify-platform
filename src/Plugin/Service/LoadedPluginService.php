<?php

declare(strict_types=1);

namespace Forumify\Plugin\Service;

use App\Kernel;
use Forumify\Plugin\PluginInterface;

class LoadedPluginService
{
    public function __construct(private readonly Kernel $kernel) { }

    /**
     * @return array<PluginInterface>
     */
    public function getLoadedPlugins(): array
    {
        $plugins = [];
        foreach ($this->kernel->getBundles() as $name => $bundle) {
            $bundleClass = $bundle->getNamespace() . '\\' . $name;
            $bundleObj = new $bundleClass();
            if ($bundleObj instanceof PluginInterface) {
                $plugins[] = $bundleObj;
            }
        }
        return $plugins;
    }
}
