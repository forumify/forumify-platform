<?php

declare(strict_types=1);

namespace Forumify\Admin\Components;

use Doctrine\ORM\QueryBuilder;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\ForumGroup;
use Forumify\Forum\Repository\ForumGroupRepository;
use Forumify\Forum\Repository\ForumRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('ForumEditor', '@Forumify/admin/components/forum_editor/forum_editor.html.twig')]
#[IsGranted('forumify.admin.forums.manage')]
class ForumEditor
{
    use DefaultActionTrait;

    #[LiveProp]
    public ?Forum $forum = null;

    public function __construct(
        private readonly ForumRepository $forumRepository,
        private readonly ForumGroupRepository $forumGroupRepository,
    ) {
    }

    /**
     * @return array<int, ForumGroup>
     */
    public function getGroups(): array
    {
        return $this->forumGroupRepository->findByParent($this->forum);
    }

    /**
     * @return array<int, Forum>
     */
    public function getUngroupedForums(): array
    {
        return $this->forumRepository->findUngroupedByParent($this->forum);
    }

    #[LiveAction]
    #[IsGranted('forumify.admin.forums.manage')]
    public function reorderGroup(#[LiveArg] int $groupId, #[LiveArg] string $direction): void
    {
        $group = $this->forumGroupRepository->find($groupId);
        if ($group === null) {
            return;
        }

        $this->forumGroupRepository->reorder($group, $direction);
    }

    #[LiveAction]
    #[IsGranted('forumify.admin.forums.manage')]
    public function reorderForum(#[LiveArg] int $forumId, #[LiveArg] string $direction): void
    {
        $forum = $this->forumRepository->find($forumId);
        if ($forum === null) {
            return;
        }

        $this->forumRepository->reorder($forum, $direction, static function (QueryBuilder $qb) use ($forum) {
            if ($forum->getGroup() !== null) {
                $qb->andWhere('e.group = :group')->setParameter('group', $forum->getGroup());
            } else {
                $qb->andWhere('e.group IS NULL');
            }
        });
    }
}
