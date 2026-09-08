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
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
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

        $this->prependEntityMapping($container, $builder);
        $this->prependMigrations($container);
        $this->prependApiResources($container, $builder);
    }

    /**
     * Registers src/Entity for doctrine and api-platform based on where this bundle is
     * actually installed, so plugins don't have to hardcode their own vendor path.
     *
     * Shipping config/packages/doctrine.yaml opts out and leaves you in full control.
     */
    private function prependEntityMapping(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $entityDir = $this->getPath() . '/src/Entity';
        if (!is_dir($entityDir) || !$builder->hasExtension('doctrine')) {
            return;
        }

        if (is_file($this->getConfigDir() . '/packages/doctrine.yaml')) {
            return;
        }

        $container->extension('doctrine', [
            'orm' => [
                'mappings' => [
                    $this->getName() => [
                        'is_bundle' => false,
                        'type' => 'attribute',
                        'dir' => $entityDir,
                        'prefix' => (new \ReflectionClass($this))->getNamespaceName() . '\\Entity',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Registers the migrations directory under the namespace the migrations themselves
     * declare. That namespace is part of the executed migration versions stored in the
     * database, so it is read from an existing migration rather than derived: renaming it
     * would make doctrine believe applied migrations still have to run.
     *
     * Shipping config/packages/doctrine_migrations.yaml opts out.
     */
    private function prependMigrations(ContainerConfigurator $container): void
    {
        $migrationsDir = $this->getPath() . '/migrations';
        if (!is_dir($migrationsDir)) {
            return;
        }

        if (is_file($this->getConfigDir() . '/packages/doctrine_migrations.yaml')) {
            return;
        }

        $container->extension('doctrine_migrations', [
            'migrations_paths' => [
                $this->getMigrationsNamespace($migrationsDir) => $migrationsDir,
            ],
        ]);
    }

    /**
     * Shipping config/packages/api_platform.yaml opts out.
     */
    private function prependApiResources(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // The whole source directory, not just entities: api resources are regularly
        // plain DTOs living elsewhere in the plugin.
        $srcDir = $this->getPath() . '/src';
        if (!is_dir($srcDir) || !$builder->hasExtension('api_platform')) {
            return;
        }

        if (is_file($this->getConfigDir() . '/packages/api_platform.yaml')) {
            return;
        }

        $container->extension('api_platform', [
            'mapping' => [
                'paths' => [$srcDir],
            ],
        ]);
    }

    /**
     * The namespace declared by the migrations already in the directory, falling back to
     * the bundle name for plugins that don't have any migrations yet.
     */
    private function getMigrationsNamespace(string $migrationsDir): string
    {
        foreach (glob($migrationsDir . '/Version*.php') ?: [] as $migration) {
            $contents = file_get_contents($migration);
            if ($contents === false) {
                continue;
            }

            if (preg_match('/^namespace\s+([^;]+);/m', $contents, $matches) === 1) {
                return trim($matches[1]);
            }
        }

        return $this->getName() . 'Migrations';
    }

    /** @inheritDoc */
    public function build(ContainerBuilder $container): void
    {
        /** @var string $environment */
        $environment = $container->getParameter('kernel.environment');

        $configDir = $this->getConfigDir();
        $locator = new FileLocator($configDir);

        $resolver = new LoaderResolver([
            new YamlFileLoader($container, $locator, $environment),
            new PhpFileLoader($container, $locator, $environment),
            new GlobFileLoader($container, $locator, $environment),
            new DirectoryLoader($container, $locator, $environment),
            new ClosureLoader($container, $environment),
        ]);

        $configLoader = new DelegatingLoader($resolver);
        $extensions = '.{php,yaml,yml}';

        $configLoader->load($configDir . '/{packages}/*' . $extensions, 'glob');
        $configLoader->load($configDir . '/{packages}/' . $environment . '/*' . $extensions, 'glob');
    }

    /**
     * @inheritDoc
     * @param array<mixed> $config
     */
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
