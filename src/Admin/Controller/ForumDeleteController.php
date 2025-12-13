<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Repository\ForumRepository;
use Forumify\Forum\Service\LastCommentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('forumify.admin.forums.manage')]
class ForumDeleteController extends AbstractController
{
    public function __construct(
        private readonly ForumRepository $forumRepository,
        private readonly LastCommentService $lastCommentService,
    ) {
    }

    #[Route('/forum/{slug:forum}/delete', 'forum_delete')]
    public function __invoke(Request $request, Forum $forum): Response
    {
        if (!$request->query->get('confirmed')) {
            return $this->render('@Forumify/admin/forum/delete.html.twig', [
                'forum' => $forum,
            ]);
        }

        $parentSlug = $forum->getParent()?->getSlug();
        $this->forumRepository->remove($forum);
        $this->lastCommentService->clearCache();

        $this->addFlash('success', 'flashes.forum_removed');
        return $this->redirectToRoute('forumify_admin_forum', ['slug' => $parentSlug]);
    }
}
