<?php

declare(strict_types=1);

namespace Forumify\Forum\Controller;

use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\ForumGroup;
use Forumify\Forum\ForumType\ForumTypeInterface;
use Forumify\Forum\Repository\ForumRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ForumController extends AbstractController
{
    /** @var array<string, ForumTypeInterface> */
    private readonly array $forumTypes;

    /**
     * @param iterable<ForumTypeInterface> $forumTypes
     */
    public function __construct(
        #[AutowireIterator('forumify.forum.type', defaultIndexMethod: 'getType')]
        iterable $forumTypes,
    ) {
        $this->forumTypes = iterator_to_array($forumTypes);
    }

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

        $template = '@Forumify/frontend/forum/list.html.twig';
        if ($forum !== null && isset($this->forumTypes[$forum->getType()])) {
            $type = $this->forumTypes[$forum->getType()];
            $template = $type->getTemplate();
        }

        return $this->render($template, [
            'forum' => $forum,
            'ungroupedForums' => $ungroupedForums,
            'groups' => $groups,
        ]);
    }
}
