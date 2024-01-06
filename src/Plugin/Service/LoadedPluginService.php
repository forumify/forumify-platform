<?php

declare(strict_types=1);

namespace Forumify\Plugin\Service;

use App\Kernel;
use Forumify\Plugin\PluginInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class LoadedPluginService
{
    public function __construct(private readonly Kernel $kernel)
    {
    }

    /**
     * @return array<BundleInterface&PluginInterface>
     */
    public function getLoadedPlugins(): array
    {
        return array_filter(
            $this->kernel->getBundles(),
            static fn (BundleInterface $bundle) => $bundle instanceof PluginInterface
        );
    }
}
