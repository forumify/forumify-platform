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

class ForumCreateController extends AbstractController
{
    #[Route('/forum/create', 'forum_create', priority: 1)]
    public function __invoke(Request $request, ForumRepository $forumRepository, ForumGroupRepository $forumGroupRepository): Response
    {
        $parentId = $request->query->get('parent');
        $parent = $parentId !== null
            ? $forumRepository->find($parentId)
            : null;

        $groupId = $request->query->get('group');
        $group = $groupId !== null
            ? $forumGroupRepository->find($groupId)
            : null;

        $forum = new Forum();
        $forum->setParent($parent);
        $forum->setGroup($group);

        $form = $this->createForm(ForumType::class, $forum);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $forum = $form->getData();
            $forumRepository->save($forum);

            $this->addFlash('success', 'flashes.forum_saved');
            return $this->redirectToRoute('forumify_admin_forum', [
                'slug' => $parent?->getSlug(),
            ]);
        }

        return $this->render('@Forumify/admin/forum/create.html.twig', [
            'form' => $form->createView(),
            'parent' => $parent,
        ]);
    }
}
