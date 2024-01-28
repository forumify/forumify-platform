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

class ForumGroupCreateController extends AbstractController
{
    #[Route('forum-group/create', 'forum_group_create')]
    public function __invoke(Request $request, ForumRepository $forumRepository, ForumGroupRepository $forumGroupRepository): Response
    {
        $parentId = $request->query->get('parent');
        $parent = $parentId !== null
            ? $forumRepository->find($parentId)
            : null;

        // TODO: use repository to get highest position instead of looping
        $siblings = $forumGroupRepository->findByParent($parent);
        $highestPosition = 0;
        foreach ($siblings as $sibling) {
            if ($sibling->getPosition() > $highestPosition) {
                $highestPosition = $sibling->getPosition();
            }
        }
        $highestPosition++;

        $group = new ForumGroup();
        $group->setParentForum($parent);
        $group->setPosition($highestPosition);

        $form = $this->createForm(ForumGroupType::class, $group);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $forumGroup = $form->getData();
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
