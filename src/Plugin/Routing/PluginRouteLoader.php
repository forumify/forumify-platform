<?php

declare(strict_types=1);

namespace Forumify\Plugin\Routing;

use App\Kernel;
use Forumify\Plugin\PluginInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\Routing\RouteCollection;

#[AutoconfigureTag('routing.loader')]
class PluginRouteLoader extends Loader
{
    private const ROUTE_LOCATION = '/config/routes.yaml';

    public function __construct(private readonly Kernel $kernel)
    {
        parent::__construct();
    }

    public function supports(mixed $resource, string $type = null): bool
    {
        return $type === 'forumify_plugin';
    }

    public function load(mixed $resource, string $type = null): RouteCollection
    {
        $routeCollection = new RouteCollection();

        foreach ($this->kernel->getBundles() as $bundle) {
            if (!$bundle instanceof PluginInterface) {
                continue;
            }

            if ($this->bundleHasRoutes($bundle)) {
                $this->loadBundleRoutes($bundle, $routeCollection);
            }
        }

        return $routeCollection;
    }

    private function bundleHasRoutes(BundleInterface $bundle): bool
    {
        $realPath = $bundle->getPath() . self::ROUTE_LOCATION;
        return file_exists($realPath);
    }

    private function loadBundleRoutes(BundleInterface $bundle, RouteCollection $collection): void
    {
        $location = '@' . $bundle->getName() . self::ROUTE_LOCATION;
        $importedRoutes = $this->import($location, 'yaml');

        $collection->addCollection($importedRoutes);
    }
}
