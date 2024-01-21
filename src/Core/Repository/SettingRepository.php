<?php

declare(strict_types=1);

namespace Forumify\Core\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Forumify\Core\Entity\Setting;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;

class SettingRepository extends AbstractRepository
{
    public const CACHE_KEY = 'settings.cache';

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

    public function get(string $key): string
    {
        $settings = $this->getSettingsFromCache();
        return $settings[$key] ?? '';
    }

    public function getJson(string $key): mixed
    {
        try {
            return json_decode($this->get($key), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }
    }

    public function getAll(): array
    {
        return $this->getSettingsFromCache();
    }

    public function set(
        string $key,
        string $value,
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

    public function setJson(
        string $key,
        mixed $value,
        bool $flush = true,
        bool $refreshCache = true
    ): void {
        try {
            $this->set($key, json_encode($value, JSON_THROW_ON_ERROR), $flush, $refreshCache);
        } catch (\JsonException) {
            $this->set($key, '', $flush, $refreshCache);
        }
    }

    public function setBulk(array $settings): void
    {
        foreach ($settings as $key => $value) {
            if (is_string($value)) {
                $this->set($key, $value, false, false);
            } else {
                $this->setJson($key, $value, false, false);
            }
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

    private function invalidateSettingsCache(): void
    {
        try {
            $this->cache->delete(self::CACHE_KEY);
        } catch (InvalidArgumentException) {
            // impossible
        }

        // warm up the cache again
        $this->getSettingsFromCache();
    }

    /**
     * @return array<string, string>
     */
    private function getSettingsFromCache(): array
    {
        try {
            return $this->cache->get(self::CACHE_KEY, $this->refreshSettingsCache(...));
        } catch (InvalidArgumentException) {
            // impossible
        }
        return [];
    }

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
