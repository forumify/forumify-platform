<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Admin\Form\ForumGroupType;
use Forumify\Forum\Entity\ForumGroup;
use Forumify\Forum\Repository\ForumGroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ForumGroupEditController extends AbstractController
{
    #[Route('forum-group/{id}/edit', 'forum_group_edit')]
    public function __invoke(Request $request, ForumGroup $group, ForumGroupRepository $forumGroupRepository): Response
    {
        $form = $this->createForm(ForumGroupType::class, $group);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $group = $form->getData();
            $forumGroupRepository->save($group);

            $this->addFlash('success', 'flashes.group_edited');
            return $this->redirectToRoute('forumify_admin_forum', [
                'slug' => $group->getParentForum()?->getSlug(),
            ]);
        }

        return $this->render('@Forumify/admin/forum/group.html.twig', [
            'form' => $form->createView(),
            'group' => $group,
        ]);
    }
}
