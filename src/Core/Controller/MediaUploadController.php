<?php

declare(strict_types=1);

namespace Forumify\Core\Controller;

use DateTime;
use Forumify\Core\Entity\Media;
use Forumify\Core\Repository\MediaRepository;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MediaUploadController extends AbstractController
{
    public function __construct(
        private readonly MediaRepository $mediaRepository,
        private readonly FilesystemOperator $mediaStorage,
        private readonly Packages $packages,
    ) {
    }

    #[Route('/media/upload', 'media_upload', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function upload(Request $request): JsonResponse
    {
        /** @var UploadedFile|null $file */
        $file = $request->files->get('file');
        if ($file === null) {
            return new JsonResponse(['error' => 'no file in request'], Response::HTTP_BAD_REQUEST);
        }

        $filename = md5_file($file->getPathname());
        $ext = $file->guessExtension() ?? $file->getClientOriginalExtension();
        if ($ext) {
            $filename .= '.' . $ext;
        }

        $destination = (new DateTime())->format('Y') . '/' . $filename;

        try {
            if ($this->mediaStorage->fileExists($destination)) {
                return $this->url($destination);
            }
            $this->mediaStorage->write($destination, $file->getContent());
        } catch (FilesystemException $ex) {
            return new JsonResponse(['error' => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $media = new Media();
        $media->setPath($destination);
        $this->mediaRepository->save($media);

        return $this->url($destination);
    }

    private function url(string $destination): JsonResponse
    {
        return new JsonResponse([
            'url' => $this->packages->getUrl($destination, 'forumify.media'),
        ]);
    }
}
