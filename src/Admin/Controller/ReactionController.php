<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Admin\Form\ReactionType;
use Forumify\Core\Service\MediaService;
use Forumify\Forum\Entity\Reaction;
use Forumify\Forum\Repository\ReactionRepository;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reactions', 'reaction')]
class ReactionController extends AbstractController
{
    public function __construct(
        private readonly ReactionRepository $reactionRepository,
        private readonly MediaService $mediaService,
        private readonly FilesystemOperator $assetStorage,
    ) {
    }

    #[Route('', name: '_list')]
    public function list(): Response
    {
        return $this->render('@Forumify/admin/reaction/reaction_list.html.twig');
    }

    #[Route('/create', '_create')]
    public function create(Request $request): Response
    {
        return $this->handleForm($request);
    }

    #[Route('/{id<\d+>}', '')]
    public function edit(Request $request, Reaction $reaction): Response
    {
        return $this->handleForm($request, $reaction);
    }

    #[Route('/{id<\d+>}/delete', '_delete')]
    public function delete(Reaction $reaction): Response
    {
        $this->reactionRepository->remove($reaction);

        $this->addFlash('success', 'flashes.reaction_removed');
        return $this->redirectToRoute('forumify_admin_reaction_list');
    }

    private function handleForm(Request $request, ?Reaction $reaction = null): Response
    {
        $form = $this->createForm(ReactionType::class, $reaction, [
            'image_required' => $reaction === null,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Reaction $reaction */
            $reaction = $form->getData();

            $newImage = $form->get('newImage')->getData();
            if ($newImage instanceof UploadedFile) {
                $image = $this->mediaService->saveToFilesystem($this->assetStorage, $newImage);
                $reaction->setImage($image);
            }

            $this->reactionRepository->save($reaction);
            $this->addFlash('success', 'flashes.reaction_saved');
            return $this->redirectToRoute('forumify_admin_reaction_list');
        }

        return $this->render('@Forumify/admin/reaction/reaction.html.twig', [
            'form' => $form->createView(),
            'reaction' => $reaction,
        ]);
    }
}
