<?php

declare(strict_types=1);

namespace Forumify\Plugin\Application\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class FrameworkCacheClearer
{
    public function __construct(private readonly string $rootDir)
    {
    }

    /**
     * @throws \Exception
     */
    public function clear(): void
    {
        $attempts = 0;
        $lastException = null;
        $success = false;

        while ($attempts < 5) {
            $attempts++;
            try {
                $this->clearUnsafe();
                $success = true;
                break;
            } catch (\Exception $ex) {
                $lastException = $ex;
            }
            // sometimes FS can be busy in cache dir. Wait increasingly amounts of time and retry...
            sleep($attempts);
        }

        if (!$success && $lastException !== null) {
            throw $lastException;
        }
    }

    private function clearUnsafe(): void
    {
        $cacheDirs = (new Finder())
            ->depth(0)
            ->in($this->rootDir . '/var/cache');

        $fs = new Filesystem();
        foreach ($cacheDirs as $cacheDir) {
            $fs->remove($cacheDir->getRealPath());
        }
    }
}
