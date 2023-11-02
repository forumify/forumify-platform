<?php

declare(strict_types=1);

namespace Forumify\Forum\Controller;

use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\ForumGroup;
use Forumify\Forum\Repository\ForumRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ForumController extends AbstractController
{
    #[Route('/forum/{slug?}', name: 'forum')]
    public function __invoke(ForumRepository $forumRepository, ?Forum $forum = null): Response
    {
        if ($forum !== null) {
            $this->denyAccessUnlessGranted(VoterAttribute::ACL->value, [
                'permission' => 'view',
                'entity' => $forum
            ]);
        }

        $ungroupedForums = [];
        $groups = [];
        $childForums = $forumRepository->findByParent($forum);
        foreach ($childForums as $childForum) {
            $group = $childForum->getGroup();
            if ($group === null) {
                $ungroupedForums[] = $childForum;
                continue;
            }
            $groups[$group->getId()] = $group;
        }

        uasort($groups, static fn (ForumGroup $a, ForumGroup $b) => $a->getPosition() - $b->getPosition());

        return $this->render('@Forumify/frontend/forum/list.html.twig', [
            'forum' => $forum,
            'ungroupedForums' => $ungroupedForums,
            'groups' => $groups,
        ]);
    }
}
