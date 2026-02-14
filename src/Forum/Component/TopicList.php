<?php

declare(strict_types=1);

namespace Forumify\Forum\Component;

use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Component\List\AbstractDoctrineList;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\Topic;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

/**
 * @extends AbstractDoctrineList<Topic>
 */
#[AsLiveComponent(name: 'TopicList', template: '@Forumify/frontend/components/topic_list.html.twig')]
class TopicList extends AbstractDoctrineList
{
    #[LiveProp]
    public Forum $forum;

    #[LiveProp]
    public string $sortMode = 'default';

    public function __construct(
        private readonly Security $security,
    ) {
    }

    protected function getEntityClass(): string
    {
        return Topic::class;
    }

    #[LiveAction]
    public function sort(#[LiveArg] string $mode): void
    {
        $this->sortMode = $mode;
    }

    /**
     * @return array<array{mode: string, icon: string}>
     */
    public function getSortModes(): array
    {
        $sortModes = [
            [
                'mode' => 'default',
                'icon' => 'funnel-simple-x',
            ],
            [
                'mode' => 'popularity',
                'icon' => 'fire',
            ],
            [
                'mode' => 'alphabetical',
                'icon' => 'sort-ascending',
            ],
            [
                'mode' => 'created_at',
                'icon' => 'calendar',
            ],
            [
                'mode' => 'views',
                'icon' => 'eye',
            ],
            [
                'mode' => 'comments',
                'icon' => 'chats',
            ],
            [
                'mode' => 'reactions',
                'icon' => 'sparkle',
            ],
        ];

        if ($this->forum->getType() === Forum::TYPE_SUPPORT) {
            $sortModes[] = [
                'mode' => 'unsolved',
                'icon' => 'question',
            ];
        }

        return $sortModes;
    }

    protected function getQuery(): QueryBuilder
    {
        $qb = $this->getBaseQueryBuilder()
            ->orderBy('t.pinned', 'DESC')
            ->groupBy('t');

        switch ($this->sortMode) {
            case 'default':
                $qb->addSelect('MAX(c.createdAt) AS HIDDEN lastCommentDate')
                    ->leftJoin('t.comments', 'c')
                    ->addOrderBy('lastCommentDate', 'DESC');
                break;
            case 'popularity':
                $qb
                    ->leftJoin('t.comments', 'c')
                    ->leftJoin('c.reactions', 'r')
                    ->addSelect('(t.views + COUNT(r) * 5 + COUNT(c) * 25) AS HIDDEN points')
                    ->addOrderBy('points', 'DESC');
                break;
            case 'alphabetical':
                $qb->addOrderBy('t.title', 'ASC');
                break;
            case 'created_at':
                $qb->addOrderBy('t.createdAt', 'DESC');
                break;
            case 'views':
                $qb->addOrderBy('t.views', 'DESC');
                break;
            case 'comments':
                $qb->addSelect('COUNT(c) AS HIDDEN commentCount')
                    ->leftJoin('t.comments', 'c')
                    ->addOrderBy('commentCount', 'DESC');
                break;
            case 'reactions':
                $qb->addSelect('COUNT(r) AS HIDDEN reactionCount')
                    ->leftJoin('t.firstComment', 'fc')
                    ->leftJoin('fc.reactions', 'r')
                    ->addOrderBy('reactionCount', 'DESC');
                break;
            case 'unsolved':
                $qb->addOrderBy('CASE
                    WHEN t.answer IS NULL THEN 0
                    ELSE 1
                    END', 'ASC');
                break;
            default:
        }

        return $qb;
    }

    protected function getTotalCount(): int
    {
        return (int)$this->getBaseQueryBuilder()
            ->select('COUNT(t)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function getBaseQueryBuilder(): QueryBuilder
    {
        $qb = $this->repository
            ->createQueryBuilder('t')
            ->where('t.forum = :forum')
            ->setParameter('forum', $this->forum);

        $canViewHidden = $this->security->isGranted(VoterAttribute::ACL->value, [
            'entity' => $this->forum,
            'permission' => 'moderate',
        ]);

        if (!$canViewHidden) {
            $qb->andWhere('t.hidden = 0');
        }

        $canOnlyShowOwnSetting = $this->forum->getDisplaySettings()->isOnlyShowOwnTopics();
        if ($canOnlyShowOwnSetting) {
            $canSeeAll = $this->security->isGranted(VoterAttribute::ACL->value, [
                'entity' => $this->forum,
                'permission' => 'show_all_topics',
            ]);
            if (!$canSeeAll) {
                $user = $this->security->getUser();
                $qb->andWhere('t.createdBy = :author')
                    ->setParameter('author', $user);
            }
        }

        return $qb;
    }
}
