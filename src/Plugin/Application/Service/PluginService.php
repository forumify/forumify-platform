<?php

declare(strict_types=1);

namespace Forumify\Plugin\Application\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Forumify\Core\ForumifyKernel;
use Forumify\Plugin\Application\Exception\PluginException;
use Forumify\Plugin\Application\Exception\PluginNotFoundException;
use Forumify\Plugin\Application\Exception\UnbootableKernelException;
use JsonException;
use Symfony\Component\Process\Process;

class PluginService
{
    private readonly Connection $connection;
    private readonly FrameworkCacheClearer $frameworkCacheClearer;
    private readonly string $rootDir;

    public function __construct(private readonly array $context)
    {
        $this->connection = DriverManager::getConnection([
            'url' => $context['DATABASE_URL'],
        ]);

        $this->rootDir = dirname($context['DOCUMENT_ROOT']);
        $this->frameworkCacheClearer = new FrameworkCacheClearer($this->rootDir);
    }

    /**
     * @throws PluginException
     */
    public function activate(int $pluginId): void
    {
        if ($this->setActive($pluginId, true) < 1) {
            throw new PluginNotFoundException($pluginId);
        }

        $this->clearFrameworkCache();
        $this->validateKernel();
        $this->postInstall();
        $this->runMigrations();
    }

    /**
     * @throws PluginException
     */
    public function deactivate(int $pluginId): void
    {
        if ($this->setActive($pluginId, false) < 1) {
            throw new PluginNotFoundException($pluginId);
        }

        $this->clearFrameworkCache();
        $this->validateKernel();
        $this->postInstall();
    }

    /**
     * @throws PluginException
     */
    public function updatePackage(string $package): void
    {
        $versions = self::getLatestVersions($this->rootDir)[$package] ?? null;
        if ($versions === null) {
            throw new PluginException("Unable to read versions for $package.");
        }

        if (($versions['latest-status'] ?? null) !== 'update-possible') {
            return; // nothing to update....
        }

        $this->require($package, $versions['latest'] ?? '*');

        $this->clearFrameworkCache();
        try {
            $this->validateKernel();
        } catch (UnbootableKernelException $ex) {
            $this->require($package, $versions['version'] ?? '*');
            throw new PluginException('Unable to require new version, rollback was successful', 0, $ex);
        }
        $this->postInstall();
        $this->runMigrations();
    }

    /**
     * @throws PluginException
     */
    public function findPackageForPlugin(int $pluginId): string
    {
        try {
            $packages = $this->connection->fetchFirstColumn('SELECT package FROM plugin WHERE id = ?', [$pluginId]);
        } catch (Exception $ex) {
            throw new PluginException($ex->getMessage(), 0, $ex);
        }

        if (empty($packages)) {
            throw new PluginNotFoundException($pluginId);
        }

        return reset($packages);
    }

    /**
     * @throws PluginException
     */
    public function updateAll(): void
    {
        $this->update();
        $this->clearFrameworkCache();
        $this->postInstall();
        $this->runMigrations();
    }

    /**
     * @throws PluginException
     */
    public function uninstallPluginFromPackage(string $package, bool $allowRollback = true): void
    {
        $this->remove($package);
        $this->clearFrameworkCache();
        $this->postInstall();

        try {
            $this->validateKernel();
        } catch (UnbootableKernelException $ex) {
            if ($allowRollback) {
                $this->installPluginFromPackage($package, false);
                throw new PluginException('Unable to boot after removing plugin. Plugin was re-installed.', 0, $ex);
            }
            throw new PluginException('Unable to boot after removing plugin.', 0, $ex);
        }
    }

    /**
     * @throws PluginException
     */
    public function installPluginFromPackage(string $package, bool $allowRollback = true): void
    {
        $this->require($package);
        $this->clearFrameworkCache();
        $this->postInstall();
        $this->runMigrations();

        try {
            $this->validateKernel();
        } catch (UnbootableKernelException $ex) {
            if ($allowRollback) {
                $this->uninstallPluginFromPackage($package, false);
                throw new PluginException('Unable to boot after installing plugin. Plugin was removed.', 0, $ex);
            }
            throw new PluginException('Unable to boot after installing plugin.', 0, $ex);
        }
    }

    private function require(string $package, ?string $version = null): void
    {
        if ($version !== null) {
            $package .= ':' . $version;
        }
        $process = new Process([
            'composer',
            'require',
            $package,
            '--no-interaction',
            '--no-scripts',
            '--working-dir',
            $this->rootDir,
        ]);
        $process->run();
    }

    private function update(?string $package = null): void
    {
        $cmd = ['composer', 'update'];
        if ($package !== null) {
            $cmd[] = $package;
            $cmd[] = '--with-all-dependencies';
        }

        $process = new Process([
            ...$cmd,
            '--no-interaction',
            '--no-scripts',
            '--working-dir',
            $this->rootDir,
        ]);
        $process->run();
    }

    private function remove(string $package): void
    {
        $process = new Process([
            'composer',
            'remove',
            $package,
            '--no-interaction',
            '--no-scripts',
            '--working-dir',
            $this->rootDir,
        ]);
        $process->run();
    }

    public static function getLatestVersions(string $rootDir): array
    {
        $process = new Process([
            'composer',
            'outdated',
            '--all',
            '--direct',
            '--format',
            'json',
            '--working-dir',
            $rootDir,
        ]);
        $process->run();
        $output = $process->getOutput();

        try {
            $versions = json_decode($output, true, 512, JSON_THROW_ON_ERROR)['installed'] ?? [];
        } catch (JsonException) {
            // a lot more needs to be broken before this can happen...
        }

        return array_combine(array_column($versions, 'name'), $versions);
    }

    /**
     * @throws PluginException
     */
    private function setActive(int $pluginId, bool $active): int
    {
        try {
            return (int)$this->connection->executeStatement('UPDATE plugin SET active = :active WHERE id = :id', [
                'active' => (int)$active,
                'id' => $pluginId,
            ]);
        } catch (Exception $ex) {
            throw new PluginException('Unable to execute query: ' . $ex->getMessage(), 0, $ex);
        }
    }

    /**
     * @throws PluginException
     */
    private function clearFrameworkCache(): void
    {
        try {
            $this->frameworkCacheClearer->clear();
        } catch (\Exception $ex) {
            throw new PluginException('Unable to clear cache: ' . $ex->getMessage(), 0, $ex);
        }
    }

    /**
     * @throws PluginException
     */
    private function validateKernel(): void
    {
        $kernel = new ForumifyKernel($this->context);
        try {
            $kernel->boot();
            $kernel->shutdown();
        } catch (\Exception $ex) {
            throw new UnbootableKernelException('Unable to boot new kernel: ' . $ex->getMessage());
        }
    }

    private function postInstall(): void
    {
        $process = new Process([
            'composer',
            'run-script',
            'post-install-cmd',
            '--no-interaction',
            '--working-dir',
            $this->rootDir,
        ]);
        $process->run();
    }

    private function runMigrations(): void
    {
        $process = new Process([
            'php',
            $this->rootDir . '/bin/console',
            'doctrine:migrations:migrate',
            '--allow-no-migration',
            '--no-interaction'
        ]);
        $process->run();
    }
}
