<?php

declare(strict_types=1);

namespace Forumify\Core\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Forumify\Core\Entity\Setting;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @extends AbstractRepository<Setting>
 */
class SettingRepository extends AbstractRepository
{
    public const CACHE_KEY = 'settings.cache';

    /** @var array<string, mixed>|null */
    private ?array $settings = null;

    public function __construct(
        private readonly CacheInterface $cache,
        ManagerRegistry $registry
    ) {
        parent::__construct($registry);
    }

    public static function getEntityClass(): string
    {
        return Setting::class;
    }

    public function get(string $key): mixed
    {
        $settings = $this->getSettingsFromCache();
        return $settings[$key] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function getAll(): array
    {
        return $this->getSettingsFromCache();
    }

    public function set(
        string $key,
        mixed $value,
        bool $flush = true,
        bool $refreshCache = true
    ): void {
        $setting = $this->find($key);
        if ($setting === null) {
            $setting = new Setting($key);
        }

        $setting->setValue($value);

        $this->save($setting, $flush);

        if ($refreshCache) {
            $this->invalidateSettingsCache();
        }
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function setBulk(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $this->set($key, $value, false, false);
        }
        $this->_em->flush();
        $this->invalidateSettingsCache();
    }

    public function unset(string $key): void
    {
        $setting = $this->find($key);
        if ($setting !== null) {
            $this->remove($setting);
            $this->invalidateSettingsCache();
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toFormData(?string $prefix = null): array
    {
        $formData = [];
        foreach ($this->refreshSettingsCache() as $key => $value) {
            if ($prefix && !str_starts_with($key, $prefix)) {
                continue;
            }
            $formData[str_replace('.', '__', $key)] = $value;
        }
        return $formData;
    }

    /**
     * @param array<string, mixed> $formData
     */
    public function handleFormData(array $formData): void
    {
        $settings = [];
        foreach ($formData as $key => $value) {
            if ($value === null) {
                continue;
            }

            $settings[str_replace('__', '.', $key)] = $value;
        }
        $this->setBulk($settings);
    }

    private function invalidateSettingsCache(): void
    {
        try {
            $this->cache->delete(self::CACHE_KEY);
            $this->settings = null;
        } catch (InvalidArgumentException) {
            // impossible
        }

        // warm up the cache again
        $this->getSettingsFromCache();
    }

    /**
     * @return array<string, mixed>
     */
    private function getSettingsFromCache(): array
    {
        if ($this->settings !== null) {
            return $this->settings;
        }

        try {
            $this->settings = $this->cache->get(self::CACHE_KEY, $this->refreshSettingsCache(...));
            return $this->settings;
        } catch (InvalidArgumentException) {
            // impossible
        }
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    private function refreshSettingsCache(): array
    {
        $configuration = $this->findAll();

        $settings = [];
        /** @var Setting $setting */
        foreach ($configuration as $setting) {
            $settings[$setting->getKey()] = $setting->getValue();
        }

        return $settings;
    }
}
