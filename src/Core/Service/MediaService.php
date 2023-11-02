<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class MediaService
{
    public function __construct(private readonly SluggerInterface $slugger)
    {
    }

    public function saveToFilesystem(FilesystemOperator $filesystem, UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $destinationFilename = uniqid($safeFilename . '-') . '.' . $file->guessExtension();

        $filesystem->write(
            $destinationFilename,
            $file->getContent(),
        );

        return $destinationFilename;
    }
}
