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
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('forumify.admin.forums.manage')]
class ForumGroupDeleteController extends AbstractController
{
    public function __construct(
        private readonly ForumGroupRepository $forumGroupRepository,
        private readonly ForumRepository $forumRepository,
    ) {
    }

    #[Route('/forum-group/{id}/delete', 'forum_group_delete')]
    public function __invoke(
        Request $request,
        ForumGroup $group
    ): Response {
        $parentSlug = $group->getParentForum()?->getSlug();

        if (!$request->get('confirmed')) {
            return $this->render('@Forumify/admin/forum/group_delete.html.twig', [
                'group' => $group,
                'parentSlug' => $parentSlug,
            ]);
        }

        $this->ungroupForums($group);
        $this->forumGroupRepository->remove($group);

        $this->addFlash('success', 'flashes.forum_group_removed');
        return $this->redirectToRoute('forumify_admin_forum', ['slug' => $parentSlug]);
    }

    public function ungroupForums(ForumGroup $group): void
    {
        $position = $this->forumRepository->getHighestPosition($group->getParentForum(), null);
        foreach ($group->getForums() as $forum) {
            $forum->setPosition(++$position);
            $forum->setGroup(null);
            $this->forumRepository->save($forum, false);
        }

        $this->forumRepository->flush();
    }
}
