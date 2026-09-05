<?php

declare(strict_types=1);

namespace Forumify\Core\Form;

use Forumify\Core\Service\FileDeletionQueue;
use Forumify\Core\Service\MediaService;
use League\Flysystem\FilesystemOperator;
use RuntimeException;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Traversable;

class UploadDataMapper implements DataMapperInterface
{
    public function __construct(
        private readonly MediaService $mediaService,
        private readonly FileDeletionQueue $deletionQueue,
        private readonly FilesystemOperator $filesystem,
        private readonly bool $multiple,
    ) {
    }

    /**
     * @param Traversable<string, FormInterface<mixed>> $forms
     */
    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
    }

    /**
     * @param Traversable<string, FormInterface<mixed>> $forms
     */
    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        $children = iterator_to_array($forms);
        $existing = UploadType::toPaths($viewData);

        $removed = array_values(array_intersect($this->getRemoved($children['removed']), $existing));
        $kept = array_values(array_diff($existing, $removed));
        $uploaded = array_map($this->write(...), $this->getUploads($children['file']));

        if (!$this->multiple && $uploaded !== []) {
            $removed = array_merge($removed, $kept);
            $kept = [];
        }

        foreach ($removed as $path) {
            $this->deletionQueue->queue($this->filesystem, $path);
        }

        $paths = array_merge($kept, $uploaded);
        $viewData = $this->multiple
            ? $paths
            : ($paths[0] ?? null);
    }

    /**
     * @param FormInterface<mixed> $form
     * @return list<string>
     */
    private function getRemoved(FormInterface $form): array
    {
        $data = $form->getData();
        if (!is_string($data) || $data === '') {
            return [];
        }

        return array_values(array_filter(array_map(trim(...), explode(',', $data))));
    }

    /**
     * @param FormInterface<mixed> $form
     * @return list<UploadedFile>
     */
    private function getUploads(FormInterface $form): array
    {
        $data = $form->getData();
        if (!is_array($data)) {
            $data = [$data];
        }

        return array_values(array_filter($data, static fn (mixed $f): bool => $f instanceof UploadedFile));
    }

    private function write(UploadedFile $file): string
    {
        $path = $this->mediaService->saveToFilesystem($this->filesystem, $file);
        if (str_contains($path, ',')) {
            throw new RuntimeException("Stored filename \"$path\" contains a comma, which cannot be represented in a simple_array field.");
        }

        return $path;
    }
}
