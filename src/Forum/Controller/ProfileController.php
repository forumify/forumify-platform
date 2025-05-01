<?php

declare(strict_types=1);

namespace Forumify\Forum\Controller;

use Forumify\Core\Entity\User;
use Forumify\Forum\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    public function __construct(
        private readonly CommentRepository $commentRepository,
    ) {
    }

    #[Route('/profile/{username}', name: 'profile')]
    public function __invoke(User $user): Response
    {
        return $this->render('@Forumify/frontend/profile/profile.html.twig', [
            'user' => $user,
            'comments' => $this->commentRepository->getUserLastComments($user),
        ]);
    }

    #[Route('/profile/{id}', 'profile_id', requirements: ['id' => '\d+'], priority: 1)]
    public function profileById(User $user): Response
    {
        return $this->redirectToRoute('forumify_forum_profile', [
            'username' => $user->getUsername()
        ], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/profile/{id}/preview', 'profile_preview', requirements: ['id' => '\d+'])]
    public function profilePreview(User $user): Response
    {
        return $this->render('@Forumify/frontend/profile/preview.html.twig', [
            'user' => $user,
        ]);
    }
}
