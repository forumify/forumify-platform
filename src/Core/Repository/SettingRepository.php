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

    public function flush(): void
    {
        parent::flush();
        $this->invalidateSettingsCache();
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
    ): void {
        $setting = $this->find($key);
        if ($setting === null) {
            $setting = new Setting($key);
        }

        $setting->setValue($value);
        $this->save($setting, $flush);
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function setBulk(array $settings, bool $flush = true): void
    {
        foreach ($settings as $key => $value) {
            $this->set($key, $value, false);
        }

        if ($flush) {
            $this->flush();
        }
    }

    public function unset(string $key, bool $flush = true): void
    {
        $setting = $this->find($key);
        if ($setting === null) {
            return;
        }

        $this->remove($setting, $flush);
    }

    /**
     * @param array<string> $settingKeys
     */
    public function unsetBulk(array $settingKeys, bool $flush = true): void
    {
        foreach ($settingKeys as $key) {
            $this->unset($key, false);
        }

        if ($flush) {
            $this->flush();
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
        $toSet = [];
        $toUnset = [];
        foreach ($formData as $key => $value) {
            $settingKey = str_replace('__', '.', $key);
            $oldValue = $this->get($settingKey);

            if ($value === null && $oldValue !== null) {
                $toUnset[] = $settingKey;
            } elseif ($value !== $oldValue) {
                $toSet[$settingKey] = $value;
            }
        }

        $this->setBulk($toSet, false);
        $this->unsetBulk($toUnset, false);
        $this->flush();
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
