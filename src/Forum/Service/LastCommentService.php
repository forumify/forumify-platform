<?php

declare(strict_types=1);

namespace Forumify\Forum\Service;

use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\UserRepository;
use Forumify\Core\Service\ACLService;
use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\Topic;
use Forumify\Forum\Repository\CommentRepository;
use Forumify\Forum\Repository\TopicVisibility;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Contracts\Service\ResetInterface;

class LastCommentService implements ResetInterface
{
    public const LAST_COMMENT_CACHE_TAG = 'forumify.forum.last_comment';

    /** @var array<int, array{id: int, createdAt: DateTime}>|null */
    private ?array $lastCommentPerForum = null;

    /** @var array<int, array{id: int, createdAt: DateTime}|null> */
    private array $lastCommentPerForumTree = [];

    /** @var array<int, Comment|null> */
    private array $lastCommentPerTopic = [];

    public function __construct(
        private readonly TagAwareAdapterInterface $cache,
        private readonly Security $security,
        private readonly CommentRepository $commentRepository,
        private readonly UserRepository $userRepository,
        private readonly ForumVisibilityService $forumVisibility,
        private readonly ACLService $aclService,
    ) {
    }

    public static function getForumCacheTag(int $forumId): string
    {
        return self::LAST_COMMENT_CACHE_TAG . '.' . $forumId;
    }

    public function clearCache(): void
    {
        $this->cache->invalidateTags([self::LAST_COMMENT_CACHE_TAG]);
        $this->reset();
    }

    public function clearCacheForForum(int $forumId): void
    {
        $this->cache->invalidateTags([self::getForumCacheTag($forumId)]);
        $this->reset();
    }

    /**
     * @param iterable<Forum> $forums
     */
    public function preload(iterable $forums): void
    {
        $ids = [];
        foreach ($forums as $forum) {
            if (!$forum->getDisplaySettings()->isShowLastCommentBy()) {
                continue;
            }

            $lastComment = $this->getLastCommentForForumTree($forum);
            if ($lastComment !== null) {
                $ids[] = $lastComment['id'];
            }
        }

        $comments = $this->commentRepository->findWithTopicAndAuthor($ids);
        $authors = array_filter(array_map(static fn (Comment $comment) => $comment->getCreatedBy(), $comments));
        $this->userRepository->preloadRoles($authors);
    }

    /**
     * @param array<Topic> $topics
     */
    public function preloadTopics(array $topics): void
    {
        $lastComments = $this->commentRepository->findLastCommentPerTopic($topics);
        foreach ($topics as $topic) {
            $this->lastCommentPerTopic[$topic->getId()] = $lastComments[$topic->getId()] ?? null;
        }
    }

    public function getLastComment(Forum|Topic $subject): ?Comment
    {
        if ($subject instanceof Topic) {
            return $this->getLastCommentForTopic($subject);
        }

        $lastComment = $this->getLastCommentForForumTree($subject);

        return $lastComment !== null
            ? $this->commentRepository->find($lastComment['id'])
            : null;
    }

    public function reset(): void
    {
        $this->lastCommentPerForum = null;
        $this->lastCommentPerForumTree = [];
        $this->lastCommentPerTopic = [];
    }

    private function getLastCommentForTopic(Topic $topic): ?Comment
    {
        if (array_key_exists($topic->getId(), $this->lastCommentPerTopic)) {
            return $this->lastCommentPerTopic[$topic->getId()];
        }

        $qb = $this->commentRepository->createQueryBuilder('c');
        try {
            return $qb
                ->join('c.topic', 't')
                ->where('t.id = :topicId')
                ->setParameter('topicId', $topic->getId())
                ->orderBy('c.createdAt', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException) {
            return null;
        }
    }

    /**
     * @return array{id: int, createdAt: DateTime}|null
     */
    private function getLastCommentForForumTree(Forum $forum): ?array
    {
        $forumId = $forum->getId();
        if (array_key_exists($forumId, $this->lastCommentPerForumTree)) {
            return $this->lastCommentPerForumTree[$forumId];
        }

        $lastComment = $this->getLastCommentPerForum()[$forumId] ?? null;
        foreach ($forum->getChildren() as $child) {
            if (!$this->aclService->can('view', $child)) {
                continue;
            }

            $childLastComment = $this->getLastCommentForForumTree($child);
            if ($childLastComment === null) {
                continue;
            }

            if ($lastComment === null || $childLastComment['createdAt'] > $lastComment['createdAt']) {
                $lastComment = $childLastComment;
            }
        }

        return $this->lastCommentPerForumTree[$forumId] = $lastComment;
    }

    /**
     * @return array<int, array{id: int, createdAt: DateTime}>
     */
    private function getLastCommentPerForum(): array
    {
        if ($this->lastCommentPerForum !== null) {
            return $this->lastCommentPerForum;
        }

        $visibility = $this->forumVisibility->getTopicVisibilityPerForum();
        $sharedVisibility = array_filter($visibility, static fn (TopicVisibility $v) => $v->includesOthers());
        $ownTopicsVisibility = array_diff_key($visibility, $sharedVisibility);

        $user = $this->security->getUser();
        $lastComments = $this->getSharedLastComments($sharedVisibility);
        if (!empty($ownTopicsVisibility) && $user instanceof User) {
            $lastComments += $this->commentRepository->findLastCommentPerForum($user, $ownTopicsVisibility);
        }

        return $this->lastCommentPerForum = $lastComments;
    }

    /**
     * @param array<int, TopicVisibility> $visibility
     * @return array<int, array{id: int, createdAt: DateTime}>
     */
    private function getSharedLastComments(array $visibility): array
    {
        $forumIdPerKey = [];
        foreach ($visibility as $forumId => $forumVisibility) {
            $forumIdPerKey[self::getCacheKey($forumId, $forumVisibility)] = $forumId;
        }

        $lastComments = [];
        $missing = [];
        foreach ($this->cache->getItems(array_keys($forumIdPerKey)) as $key => $item) {
            $forumId = $forumIdPerKey[$key];
            if (!$item->isHit()) {
                $missing[$forumId] = $item;
                continue;
            }

            $lastComment = $item->get();
            if ($lastComment !== null) {
                $lastComments[$forumId] = $lastComment;
            }
        }

        if (empty($missing)) {
            return $lastComments;
        }

        $resolved = $this->commentRepository->findLastCommentPerForum(null, array_intersect_key($visibility, $missing));
        foreach ($missing as $forumId => $item) {
            $lastComment = $resolved[$forumId] ?? null;
            $item->set($lastComment);
            $item->tag([self::LAST_COMMENT_CACHE_TAG, self::getForumCacheTag($forumId)]);
            $this->cache->saveDeferred($item);

            if ($lastComment !== null) {
                $lastComments[$forumId] = $lastComment;
            }
        }
        $this->cache->commit();

        return $lastComments;
    }

    private static function getCacheKey(int $forumId, TopicVisibility $visibility): string
    {
        return sprintf(
            'forumify.forum.%d.last_comment.%s',
            $forumId,
            $visibility->includesHidden() ? 'all' : 'visible',
        );
    }
}
