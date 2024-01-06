<?php

declare(strict_types=1);

namespace Forumify\Plugin;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\ClosureLoader;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\DirectoryLoader;
use Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use Symfony\Component\DependencyInjection\Loader\IniFileLoader;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * @internal
 *
 * Extend from AbstractForumifyPlugin instead!
 * @see AbstractForumifyPlugin
 */
abstract class AbstractForumifyBundle extends AbstractBundle
{
    /** @inheritDoc */
    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $environment = $container->env();
        $configDir = $this->getConfigDir();

        $container->import($configDir . '/parameters.yaml', ignoreErrors: true);
        $container->import($configDir . '/{parameters}_' . $environment . '.yaml', ignoreErrors: true);
    }

    /** @inheritDoc */
    public function build(ContainerBuilder $container): void
    {
        $environment = (string)$container->getParameter('kernel.environment');

        $configDir = $this->getConfigDir();
        $locator = new FileLocator($configDir);

        $resolver = new LoaderResolver([
            new XmlFileLoader($container, $locator),
            new YamlFileLoader($container, $locator),
            new IniFileLoader($container, $locator),
            new PhpFileLoader($container, $locator),
            new GlobFileLoader($container, $locator),
            new DirectoryLoader($container, $locator),
            new ClosureLoader($container),
        ]);

        $configLoader = new DelegatingLoader($resolver);
        $extensions = '.{php,xml,yaml,yml}';

        $configLoader->load($configDir . '/{packages}/*' . $extensions, 'glob');
        $configLoader->load($configDir . '/{packages}/' . $environment . '/*' . $extensions, 'glob');
    }

    /** @inheritDoc */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $environment = $container->env();
        $configDir = $this->getConfigDir();

        $container->import($configDir . '/services.yaml');
        $container->import($configDir . '/{services}_' . $environment . '.yaml');
    }

    private function getConfigDir(): string
    {
        return $this->getPath() . '/config';
    }
}
