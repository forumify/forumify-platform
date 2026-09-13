<?php

declare(strict_types=1);

namespace Forumify\Forum\Controller;

use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\ForumGroup;
use Forumify\Forum\ForumType\ForumTypeInterface;
use Forumify\Forum\Service\ForumTreeService;
use Forumify\Forum\Service\LastCommentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ForumController extends AbstractController
{
    /**
     * @param iterable<string, ForumTypeInterface> $forumTypes
     */
    public function __construct(
        #[AutowireIterator('forumify.forum.type')]
        private readonly iterable $forumTypes,
    ) {
    }

    #[Route('/forum/{slug:forum?}', name: 'forum')]
    public function __invoke(
        ForumTreeService $forumTree,
        LastCommentService $lastCommentService,
        ?Forum $forum = null,
    ): Response {
        if ($forum !== null) {
            $this->denyAccessUnlessGranted(VoterAttribute::ACL->value, [
                'permission' => 'view',
                'entity' => $forum,
            ]);
        }

        $ungroupedForums = [];
        $groups = [];
        $groupedForums = [];
        $childForums = $forumTree->getChildren($forum);
        foreach ($childForums as $childForum) {
            $group = $childForum->getGroup();
            if ($group === null) {
                $ungroupedForums[] = $childForum;
                continue;
            }

            $groups[$group->getId()] = $group;
            // saves lazy loading the group's forum collection in the template
            $groupedForums[$group->getId()][] = $childForum;
        }

        uasort($groups, static fn (ForumGroup $a, ForumGroup $b) => $a->getPosition() - $b->getPosition());
        $lastCommentService->preload($childForums);

        $template = $this
            ->getForumType($forum?->getType())
            ?->getTemplate() ?? '@Forumify/frontend/forum/list.html.twig';

        return $this->render($template, [
            'forum' => $forum,
            'ungroupedForums' => $ungroupedForums,
            'groups' => $groups,
            'groupedForums' => $groupedForums,
        ]);
    }

    private function getForumType(?string $type): ?ForumTypeInterface
    {
        if ($type === null) {
            return null;
        }

        foreach ($this->forumTypes as $forumType) {
            if ($forumType::getType() === $type) {
                return $forumType;
            }
        }
        return null;
    }
}
