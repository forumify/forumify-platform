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
    }

    /**
     * @throws PluginException
     */
    public function updatePlugin(int $pluginId): void
    {
        try {
            $packages = $this->connection->fetchFirstColumn('SELECT package FROM plugin WHERE id = ?', [$pluginId]);
        } catch (Exception $ex) {
            throw new PluginException($ex->getMessage(), 0, $ex);
        }

        if (empty($packages)) {
            throw new PluginNotFoundException($pluginId);
        }

        $package = reset($packages);
        $versions = self::getLatestVersions($this->rootDir)[$package] ?? null;
        if ($versions === null) {
            throw new PluginException("Unable to read versions for $package.");
        }

        $this->require($package, $versions['latest'] ?? '*');

        $this->clearFrameworkCache();
        try {
            $this->validateKernel();
        } catch (UnbootableKernelException $ex) {
            $this->require($package, $versions['version'] ?? '*');
            throw new PluginException('Unable to require new version, rollback was successful', 0, $ex);
        }
    }

    private function require(string $package, string $version): void
    {
        $process = new Process(['composer', 'require', "{$package}:{$version}", '--working-dir', $this->rootDir]);
        $process->run();
    }

    public static function getLatestVersions(string $rootDir): array
    {
        $process = new Process(['composer', 'outdated', '--all', '--direct', '--format', 'json', '--working-dir', $rootDir]);
        $process->run();
        $output = $process->getOutput();

        $versions = json_decode($output, true, 512, JSON_THROW_ON_ERROR)['installed'] ?? [];
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
}
