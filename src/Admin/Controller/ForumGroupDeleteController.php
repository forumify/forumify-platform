<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Forum\Entity\ForumGroup;
use Forumify\Forum\Repository\ForumGroupRepository;
use Forumify\Forum\Repository\ForumRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ForumGroupDeleteController extends AbstractController
{
    #[Route('/forum-group/{id}/delete', 'forum_group_delete')]
    public function __invoke(
        Request $request,
        ForumGroupRepository $forumGroupRepository,
        ForumRepository $forumRepository,
        ForumGroup $group
    ): Response {
        $parentSlug = $group->getParentForum()?->getSlug();

        if (!$request->get('confirmed')) {
            return $this->render('@Forumify/admin/forum/group_delete.html.twig', [
                'group' => $group,
                'parentSlug' => $parentSlug,
            ]);
        }

        foreach ($group->getForums() as $forum) {
            $forum->setGroup(null);
            $forumRepository->save($forum);
        }

        $forumGroupRepository->remove($group);
        $this->addFlash('success', 'flashes.forum_group_removed');
        return $this->redirectToRoute('forumify_admin_forum', ['slug' => $parentSlug]);
    }
}
