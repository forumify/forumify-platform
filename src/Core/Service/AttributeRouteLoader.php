<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use Forumify\Core\Attribute\AsFrontend;
use Forumify\Core\Controller\FrontendController;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Routing\RouteCollection;

#[AutoconfigureTag('routing.loader')]
class AttributeRouteLoader extends Loader
{
    public function __construct(
        /**
         * @var iterable<AsFrontend>
         */
        #[AutowireIterator('forumify.frontend')]
        private readonly iterable $frontends
    ) {
        parent::__construct();
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return $type === 'frontend_attribute';
    }

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        $routes = new RouteCollection();

        foreach ($this->frontends as $frontend) {
            $class = get_class($frontend);
            $reflection = new \ReflectionClass($class);
            $attributes = $reflection->getAttributes(AsFrontend::class);

            /** @var AsFrontend|null $frontendAttribute */
            $frontendAttribute = ($attributes[0] ?? null)->newInstance();
            if ($frontendAttribute === null) {
                continue;
            }

            $route = $frontendAttribute->route;
            $route->addDefaults([
                '_controller' => FrontendController::class,
                '_frontend' => $class,
            ]);

            $routes->add($frontendAttribute->name, $route);
        }

        return $routes;
    }
}
