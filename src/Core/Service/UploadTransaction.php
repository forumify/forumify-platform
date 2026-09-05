<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\Service\ResetInterface;

class UploadTransaction implements ResetInterface
{
    /** @var list<array{FilesystemOperator, string, UploadedFile}> */
    private array $stagedUploads = [];

    /** @var list<array{FilesystemOperator, string}> */
    private array $stagedDeletions = [];

    /** @var list<array{FilesystemOperator, string}> */
    private array $written = [];

    public function __construct(
        private readonly MediaService $mediaService,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function stageUpload(FilesystemOperator $filesystem, UploadedFile $file): string
    {
        $path = $this->mediaService->generatePath($file);
        $this->stagedUploads[] = [$filesystem, $path, $file];

        return $path;
    }

    public function stageDeletion(FilesystemOperator $filesystem, string $path): void
    {
        $this->stagedDeletions[] = [$filesystem, $path];
    }

    public function writeUploads(): void
    {
        $stagedUploads = $this->stagedUploads;
        $this->stagedUploads = [];

        foreach ($stagedUploads as [$filesystem, $path, $file]) {
            $filesystem->write($path, $file->getContent());
            $this->written[] = [$filesystem, $path];
        }
    }

    public function discard(): void
    {
        $this->stagedUploads = [];
        $this->stagedDeletions = [];
    }

    public function commit(): void
    {
        $stagedDeletions = $this->stagedDeletions;
        $this->reset();

        $this->delete($stagedDeletions);
    }

    public function rollback(): void
    {
        $written = $this->written;
        $this->reset();

        $this->delete($written);
    }

    public function reset(): void
    {
        $this->stagedUploads = [];
        $this->stagedDeletions = [];
        $this->written = [];
    }

    /**
     * @param list<array{FilesystemOperator, string}> $files
     */
    private function delete(array $files): void
    {
        foreach ($files as [$filesystem, $path]) {
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
}
