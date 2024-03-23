<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Admin\Form\BadgeType;
use Forumify\Core\Service\MediaService;
use Forumify\Forum\Entity\Badge;
use Forumify\Forum\Repository\BadgeRepository;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/badges', 'badge')]
class BadgeController extends AbstractController
{
    public function __construct(
        private readonly BadgeRepository $badgeRepository,
        private readonly MediaService $mediaService,
        private readonly FilesystemOperator $assetStorage,
    ) {
    }

    #[Route('', name: '_list')]
    public function list(): Response
    {
        return $this->render('@Forumify/admin/badge/badge_list.html.twig');
    }

    #[Route('/create', '_create')]
    public function create(Request $request): Response
    {
        return $this->handleForm($request);
    }

    #[Route('/{id<\d+>}', '')]
    public function edit(Request $request, Badge $badge): Response
    {
        return $this->handleForm($request, $badge);
    }

    #[Route('/{id<\d+>}/delete', '_delete')]
    public function delete(Badge $badge): Response
    {
        $this->badgeRepository->remove($badge);

        $this->addFlash('success', 'flashes.reaction_removed');
        return $this->redirectToRoute('forumify_admin_reaction_list');
    }

    private function handleForm(Request $request, ?Badge $badge = null): Response
    {
        $form = $this->createForm(BadgeType::class, $badge, [
            'image_required' => $badge === null,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Badge $badge */
            $badge = $form->getData();

            $newImage = $form->get('newImage')->getData();
            if ($newImage instanceof UploadedFile) {
                $image = $this->mediaService->saveToFilesystem($this->assetStorage, $newImage);
                $badge->setImage($image);
            }

            $this->badgeRepository->save($badge);
            $this->addFlash('success', 'flashes.badge_saved');
            return $this->redirectToRoute('forumify_admin_badge_list');
        }

        return $this->render('@Forumify/admin/badge/badge.html.twig', [
            'form' => $form->createView(),
            'badge' => $badge,
        ]);
    }
}
