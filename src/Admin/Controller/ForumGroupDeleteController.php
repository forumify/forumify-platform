<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Admin\Service\ForumGroupDeleteService;
use Forumify\Forum\Entity\ForumGroup;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('forumify.admin.forums.manage')]
class ForumGroupDeleteController extends AbstractController
{
    public function __construct(
        private readonly ForumGroupDeleteService $forumGroupDeleteService,
    ) {
    }

    #[Route('/forum-group/{id}/delete', 'forum_group_delete')]
    public function __invoke(
        Request $request,
        ForumGroup $group
    ): Response {
        $parentSlug = $group->getParentForum()?->getSlug();

        if (!$request->query->get('confirmed')) {
            return $this->render('@Forumify/admin/forum/group_delete.html.twig', [
                'group' => $group,
                'parentSlug' => $parentSlug,
            ]);
        }

        $this->forumGroupDeleteService->deleteForumGroup($group);

        $this->addFlash('success', 'flashes.forum_group_removed');
        return $this->redirectToRoute('forumify_admin_forum', ['slug' => $parentSlug]);
    }
}
