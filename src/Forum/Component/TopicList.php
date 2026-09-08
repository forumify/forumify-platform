<?php

declare(strict_types=1);

namespace Forumify\Forum\Component;

use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Component\List\AbstractDoctrineList;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\UserRepository;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\Topic;
use Forumify\Forum\Repository\CommentReactionRepository;
use Forumify\Forum\Repository\TopicRepository;
use Forumify\Forum\Service\LastCommentService;
use Forumify\Forum\Service\TopicReadMarkerService;
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

    #[LiveProp]
    public bool $showControls = true;

    /** @var array<int, int> */
    private array $commentCounts = [];
    /** @var array<int, int> */
    private array $reactionCounts = [];

    public function __construct(
        private readonly Security $security,
        private readonly TopicRepository $topicRepository,
        private readonly CommentReactionRepository $commentReactionRepository,
        private readonly LastCommentService $lastCommentService,
        private readonly TopicReadMarkerService $topicReadMarkerService,
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * @return array<Topic>
     */
    protected function getData(): array
    {
        /** @var array<Topic> $topics */
        $topics = parent::getData();
        $this->preload($topics);

        return $topics;
    }

    public function getCommentCount(Topic $topic): int
    {
        return $this->commentCounts[$topic->getId()] ?? $topic->getComments()->count();
    }

    public function getReactionCount(Topic $topic): int
    {
        $firstComment = $topic->getFirstComment();
        if ($firstComment === null) {
            return 0;
        }

        return $this->reactionCounts[$firstComment->getId()] ?? $firstComment->getReactions()->count();
    }

    /**
     * Resolves everything the list renders per topic for the whole page at once.
     *
     * @param array<Topic> $topics
     */
    private function preload(array $topics): void
    {
        if (empty($topics)) {
            return;
        }

        $this->topicRepository->preloadTags($topics);
        $this->commentCounts = $this->topicRepository->countCommentsPerTopic($topics);

        $firstComments = array_filter(array_map(static fn (Topic $topic) => $topic->getFirstComment(), $topics));
        $this->reactionCounts = $this->commentReactionRepository->countPerComment($firstComments);

        $this->lastCommentService->preloadTopics($topics);

        $user = $this->security->getUser();
        if ($user instanceof User) {
            $this->topicReadMarkerService->preload($user, $topics);
        }

        $authors = array_map(static fn (Topic $topic) => $topic->getCreatedBy(), $topics);
        $lastCommentAuthors = array_map(
            fn (Topic $topic) => $this->lastCommentService->getLastComment($topic)?->getCreatedBy(),
            $topics,
        );
        $this->userRepository->preloadRoles(array_filter([...$authors, ...$lastCommentAuthors]));
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
        return static::getAvailableSortModes($this->forum);
    }

    /**
     * @return array<array{mode: string, icon: string}>
     */
    public static function getAvailableSortModes(?Forum $forum = null): array
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

        if ($forum?->getType() === Forum::TYPE_SUPPORT) {
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
