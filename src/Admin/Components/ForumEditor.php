<?php

declare(strict_types=1);

namespace Forumify\Admin\Components;

use Doctrine\ORM\EntityManagerInterface;
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
        private readonly EntityManagerInterface $em,
        private readonly ForumGroupRepository $forumGroupRepository,
    ) {
    }

    public function getGroups(): array
    {
        return $this->forumGroupRepository->findByParent($this->forum);
    }

    public function getUngroupedForums(): array
    {
        return $this->forumRepository->findUngroupedByParent($this->forum);
    }

    #[LiveAction]
    public function reorderGroup(#[LiveArg] int $groupId, #[LiveArg] string $direction): void
    {
        $group = $this->forumGroupRepository->find($groupId);
        if ($group === null) {
            return;
        }

        $predicate = $direction === 'up' ? '<' : '>';
        $siblings = $this->forumGroupRepository->createQueryBuilder('fg')
            ->where("fg.position $predicate :position")
            ->setParameter('position', $group->getPosition())
            ->orderBy('fg.position', $direction === 'up' ? 'DESC' : 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        /** @var ForumGroup|false $toSwap */
        $toSwap = reset($siblings);
        if ($toSwap === false) {
            return;
        }

        $this->swapPositions($group, $toSwap, $direction);
        $this->forumGroupRepository->saveAll([$group, $toSwap]);
    }

    #[LiveAction]
    public function reorderForum(#[LiveArg] int $forumId, #[LiveArg] string $direction): void
    {
        $forum = $this->forumRepository->find($forumId);
        if ($forum === null) {
            return;
        }

        $predicate = $direction === 'up' ? '<' : '>';
        $qb = $this->forumRepository->createQueryBuilder('f')
            ->where("f.position $predicate :position")
            ->setParameter('position', $forum->getPosition())
            ->orderBy('f.position', $direction === 'up' ? 'DESC' : 'ASC')
            ->setMaxResults(1);

        if ($forum->getGroup() !== null) {
            $qb->andWhere('f.group = :group')
                ->setParameter('group', $forum->getGroup());
        } else {
            $qb->andWhere('f.group IS NULL');
        }

        $siblings = $qb->getQuery()->getResult();

        /** @var Forum|false $toSwap */
        $toSwap = reset($siblings);
        if ($toSwap === false) {
            return;
        }

        $this->swapPositions($forum, $toSwap, $direction);
        $this->forumRepository->saveAll([$forum, $toSwap]);
    }

    private function swapPositions(
        Forum|ForumGroup $entity,
        Forum|ForumGroup $toSwap,
        string $direction
    ): void {
        $oldPosition = $entity->getPosition();
        $newPosition = $toSwap->getPosition();
        if ($newPosition === $oldPosition) {
            $newPosition += $direction === 'up' ? -1 : 1;
        }

        $toSwap->setPosition($oldPosition);
        $entity->setPosition($newPosition);
    }
}
