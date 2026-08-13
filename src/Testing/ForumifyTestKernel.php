<?php

declare(strict_types=1);

namespace Forumify\Testing;

use Composer\InstalledVersions;
use Forumify\Core\ForumifyKernel;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * Kernel for forumify test applications, used by the platform itself and by plugins.
 *
 * It boots the standard forumify bundle set against the configuration shipped in
 * the testing/config directory, so test applications no longer maintain their own copy.
 * Every installed forumify plugin is registered automatically, including the package
 * under test, by reading `extra.forumify-plugin-class` from its composer.json.
 *
 * Point KERNEL_CLASS at this class in your .env, or extend it if you need to change
 * the bundle list. Configuration can be overridden per test application by placing
 * files in its own config/ directory; those are imported last and therefore win.
 */
class ForumifyTestKernel extends ForumifyKernel
{
    private const COMPOSER_PLUGIN_TYPE = 'forumify-plugin';

    public function __construct(string $env = 'test', bool $debug = false, ?string $projectDir = null)
    {
        $projectDir ??= self::locateTestAppDir();

        // api-platform maps %kernel.project_dir%/src and refuses to boot when it is
        // missing. A plugin's test application has no sources of its own, so rather than
        // making every plugin commit an empty directory, create it on the fly.
        if (!is_dir($projectDir . '/src')) {
            mkdir($projectDir . '/src', 0777, true);
        }

        parent::__construct(['APP_ENV' => $env, 'APP_DEBUG' => $debug], $projectDir);
    }

    /**
     * The configuration shipped with the test kit.
     *
     * It lives outside src/ because API Platform scans the platform's source directory
     * for resource mapping and would try to parse these files as resource definitions.
     */
    public static function configDir(): string
    {
        return \dirname(__DIR__, 2) . '/testing/config';
    }

    /**
     * The test application lives in tests/ of the package under test.
     */
    public static function locateTestAppDir(): string
    {
        $rootDir = realpath((string)InstalledVersions::getRootPackage()['install_path']);
        if ($rootDir === false) {
            throw new \RuntimeException('Unable to locate the root package directory.');
        }

        return $rootDir . '/tests';
    }

    /** @inheritDoc */
    public function registerBundles(): iterable
    {
        yield from $this->registerCoreBundles();
        yield from $this->registerPluginBundles();
    }

    /**
     * The bundles a forumify application always needs.
     *
     * @return iterable<BundleInterface>
     */
    protected function registerCoreBundles(): iterable
    {
        yield from [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Forumify\ForumifyBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Twig\Extra\TwigExtraBundle\TwigExtraBundle(),
            new \Symfony\WebpackEncoreBundle\WebpackEncoreBundle(),
            new \Symfony\UX\TwigComponent\TwigComponentBundle(),
            new \Symfony\UX\LiveComponent\LiveComponentBundle(),
            new \Symfony\UX\Autocomplete\AutocompleteBundle(),
            new \Symfony\UX\StimulusBundle\StimulusBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new \League\FlysystemBundle\FlysystemBundle(),
            new \Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \DAMA\DoctrineTestBundle\DAMADoctrineTestBundle(),
            new \ApiPlatform\Symfony\Bundle\ApiPlatformBundle(),
            new \Zenstruck\Foundry\ZenstruckFoundryBundle(),
            new \Liip\ImagineBundle\LiipImagineBundle(),
        ];
    }

    /**
     * Every installed forumify plugin, including the root package when it is one.
     *
     * Note that registering a plugin's bundle is not the same as activating it: routes,
     * permissions and settings only come to life once the plugin is active in the
     * database. Use `forumify:plugins:test-setup` to prepare the test database.
     *
     * @return iterable<BundleInterface>
     */
    protected function registerPluginBundles(): iterable
    {
        foreach (self::getInstalledPluginClasses() as $pluginClass) {
            yield new $pluginClass();
        }
    }

    /**
     * @return array<class-string<BundleInterface>>
     */
    public static function getInstalledPluginClasses(): array
    {
        $packages = InstalledVersions::getInstalledPackagesByType(self::COMPOSER_PLUGIN_TYPE);

        $pluginClasses = [];
        foreach ($packages as $package) {
            $installPath = InstalledVersions::getInstallPath($package);
            if ($installPath === null) {
                continue;
            }

            $composerJson = @file_get_contents($installPath . '/composer.json');
            if ($composerJson === false) {
                continue;
            }

            try {
                /** @var array{extra?: array{forumify-plugin-class?: string}} $decoded */
                $decoded = json_decode($composerJson, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                continue;
            }

            $pluginClass = $decoded['extra']['forumify-plugin-class'] ?? null;
            if ($pluginClass !== null && is_a($pluginClass, BundleInterface::class, true)) {
                $pluginClasses[] = $pluginClass;
            }
        }

        return $pluginClasses;
    }

    /**
     * Imports the shipped test configuration, then anything the test application
     * itself defines so it can override the defaults.
     */
    private function configureContainer(ContainerConfigurator $container): void
    {
        $container->import(self::configDir() . '/packages/*.yaml');
        $container->import(self::configDir() . '/services.yaml');

        $localConfigDir = $this->getProjectDir() . '/config';
        $container->import($localConfigDir . '/packages/*.yaml', ignoreErrors: true);
        $container->import($localConfigDir . '/services.yaml', ignoreErrors: true);
    }

    private function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import(self::configDir() . '/routes/*.yaml');
        $routes->import($this->getProjectDir() . '/config/routes/*.yaml', ignoreErrors: true);
    }
}
