<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Admin\Form\ForumType;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Repository\ForumGroupRepository;
use Forumify\Forum\Repository\ForumRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ForumController extends AbstractController
{
    #[Route('/forum/{slug?}', 'forum')]
    public function __invoke(
        Request $request,
        ForumRepository $forumRepository,
        ForumGroupRepository $forumGroupRepository,
        ?Forum $forum = null
    ): Response {
        $form = $forum !== null ?
            $this->createForm(ForumType::class, $forum)
            : null;

        if ($form !== null) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $updated = $form->getData();
                $forumRepository->save($updated);

                $this->addFlash('success', 'flashes.forum_saved');
                return $this->redirectToRoute('forumify_admin_forum', [
                    'slug' => $updated->getSlug(),
                ]);
            }
        }

        $groups = $forumGroupRepository->findByParent($forum);
        $ungroupedForums = $forumRepository->findUngroupedByParent($forum);

        return $this->render('@Forumify/admin/forum/forum.html.twig', [
            'form' => $form?->createView(),
            'forum' => $forum,
            'groups' => $groups,
            'ungroupedForums' => $ungroupedForums
        ]);
    }
}
