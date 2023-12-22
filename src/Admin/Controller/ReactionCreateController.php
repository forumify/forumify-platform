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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReactionCreateController extends AbstractController
{
    #[Route('/reactions/create', 'reaction_create')]
    public function __invoke(
        ReactionRepository $reactionRepository,
        MediaService $mediaService,
        FilesystemOperator $assetStorage,
    ): Response {
        $form = $this->createForm(ReactionType::class);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Reaction $reaction */
            $reaction = $form->getData();

            $newImage = $form->get('newImage')->getData();
            if ($newImage instanceof UploadedFile) {
                $image = $mediaService->saveToFilesystem($assetStorage, $newImage);
                $reaction->setImage($image);
            }

            $reactionRepository->save($reaction);
            $this->addFlash('success', 'flashes.reaction_saved');
            return $this->redirectToRoute('forumify_admin_reaction_list');
        }

        return $this->render('@Forumify/admin/reaction/reaction.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
