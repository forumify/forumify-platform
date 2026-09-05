<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Service\ResetInterface;

class FileDeletionQueue implements ResetInterface
{
    /** @var list<array{FilesystemOperator, string}> */
    private array $queued = [];

    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function queue(FilesystemOperator $filesystem, string $path): void
    {
        $this->queued[] = [$filesystem, $path];
    }

    public function commit(): void
    {
        $queued = $this->queued;
        $this->queued = [];

        foreach ($queued as [$filesystem, $path]) {
            try {
                $filesystem->delete($path);
            } catch (FilesystemException $e) {
                $this->logger->warning('Unable to delete "{path}": {error}', [
                    'path' => $path,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function discard(): void
    {
        $this->queued = [];
    }

    public function reset(): void
    {
        $this->queued = [];
    }
}
