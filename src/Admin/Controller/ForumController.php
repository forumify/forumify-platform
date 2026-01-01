<?php

declare(strict_types=1);

namespace Forumify\Admin\Controller;

use Forumify\Admin\Form\ForumType;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Repository\ForumRepository;
use Forumify\Forum\Service\LastCommentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('forumify.admin.forums.manage')]
class ForumController extends AbstractController
{
    #[Route('/forum/{slug:forum?}', 'forum')]
    public function __invoke(
        Request $request,
        ForumRepository $forumRepository,
        LastCommentService $lastCommentService,
        ?Forum $forum = null
    ): Response {
        if ($forum === null) {
            return $this->render('@Forumify/admin/forum/forum.html.twig', [
                'form' => null,
                'forum' => null,
            ]);
        }

        $form = $this->createForm(ForumType::class, $forum);

        $oldGroupId = $forum->getGroup()?->getId();
        $oldParentId = $forum->getParent()?->getId();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Forum $updated */
            $updated = $form->getData();

            $parentChanged = $updated->getParent()?->getId() !== $oldParentId;
            if ($parentChanged) {
                $updated->setGroup(null);
                $lastCommentService->clearCache();
            }

            if ($parentChanged || $updated->getGroup()?->getId() !== $oldGroupId) {
                $position = $forumRepository->getHighestPosition($updated);
                $updated->setPosition($position + 1);
            }

            $forumRepository->save($updated);

            $this->addFlash('success', 'flashes.forum_saved');
            return $this->redirectToRoute('forumify_admin_forum', [
                'slug' => $updated->getSlug(),
            ]);
        }

        return $this->render('@Forumify/admin/forum/forum.html.twig', [
            'form' => $form->createView(),
            'forum' => $forum,
        ]);
    }
}
