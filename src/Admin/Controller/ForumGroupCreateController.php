<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Admin\Form\ForumGroupType;
use Forumify\Forum\Entity\ForumGroup;
use Forumify\Forum\Repository\ForumGroupRepository;
use Forumify\Forum\Repository\ForumRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('forumify.admin.forums.manage')]
class ForumGroupCreateController extends AbstractController
{
    #[Route('forum-group/create', 'forum_group_create')]
    public function __invoke(Request $request, ForumRepository $forumRepository, ForumGroupRepository $forumGroupRepository): Response
    {
        $parentId = $request->query->get('parent');
        $parent = $parentId !== null
            ? $forumRepository->find($parentId)
            : null;

        $form = $this->createForm(ForumGroupType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $position = $forumGroupRepository->getHighestPosition($parent) + 1;

            $forumGroup = $form->getData();
            $forumGroup->setParentForum($parent);
            $forumGroup->setPosition($position);
            $forumGroupRepository->save($forumGroup);

            $this->addFlash('success', 'flashes.forum_group_saved');
            return $this->redirectToRoute('forumify_admin_forum', $parent === null ? [] : [
                'slug' => $parent->getSlug()
            ]);
        }

        return $this->render('@Forumify/admin/forum/group.html.twig', [
            'form' => $form->createView(),
            'parent' => $parent,
        ]);
    }
}
