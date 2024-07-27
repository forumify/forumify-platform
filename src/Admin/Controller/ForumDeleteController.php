<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Admin\Service\ForumDeleteService;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Repository\ForumRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('forumify.admin.forums.manage')]
class ForumDeleteController extends AbstractController
{
    #[Route('/forum/{slug}/delete', 'forum_delete')]
    public function __invoke(Request $request, ForumRepository $forumRepository, ForumDeleteService $forumDeleteService, Forum $forum): Response
    {
        if (!$request->get('confirmed')) {
            return $this->render('@Forumify/admin/forum/delete.html.twig', [
                'forum' => $forum,
            ]);
        }

        $parentSlug = $forum->getParent()?->getSlug();
        $forumDeleteService->deleteForum($forum);
        $this->addFlash('success', 'flashes.forum_removed');
        return $this->redirectToRoute('forumify_admin_forum', ['slug' => $parentSlug]);
    }
}
