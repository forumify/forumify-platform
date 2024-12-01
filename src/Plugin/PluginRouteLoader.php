<?php

declare(strict_types=1);

namespace Forumify\Plugin;

use Forumify\Core\Repository\PluginRepository;
use Forumify\Plugin\Entity\Plugin;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\Routing\RouteCollection;

#[AutoconfigureTag('routing.loader')]
class PluginRouteLoader extends Loader
{
    private const ROUTE_LOCATION = '/config/routes.yaml';

    public function __construct(private readonly PluginRepository $pluginRepository)
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

        foreach ($this->pluginRepository->findByActive() as $plugin) {
            $bundleClass = $plugin->getPluginClass();
            $bundle = new $bundleClass();

            if (!$bundle instanceof AbstractForumifyBundle) {
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
