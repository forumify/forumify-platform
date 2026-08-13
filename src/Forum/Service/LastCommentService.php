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
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Service\ResetInterface;

class LastCommentService implements ResetInterface
{
    public const LAST_COMMENT_CACHE_TAG = 'forumify.forum.last_comment';

    /** @var array<int, array{id: int, createdAt: DateTime}>|null */
    private ?array $lastCommentPerForum = null;

    /**
     * @param TagAwareCacheInterface $cache
     */
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly Security $security,
        private readonly CommentRepository $commentRepository,
        private readonly UserRepository $userRepository,
        private readonly ForumVisibilityService $forumVisibility,
        private readonly ACLService $aclService,
    ) {
    }

    public function clearCache(): void
    {
        $this->cache->invalidateTags([self::LAST_COMMENT_CACHE_TAG]);
    }

    /**
     * Loads the last comment of several forums, with their topic and author, up front so the
     * getLastComment() calls that follow are served from the identity map.
     *
     * @param iterable<Forum> $forums
     */
    public function preload(iterable $forums): void
    {
        /** @var User|null $user */
        $user = $this->security->getUser();
        $userId = (string)($user?->getId() ?? 'guest');

        $ids = [];
        foreach ($forums as $forum) {
            if (!$forum->getDisplaySettings()->isShowLastCommentBy()) {
                continue;
            }

            $lastComment = $this->getLastCommentForForumTree($forum, $userId);
            if ($lastComment !== null) {
                $ids[] = $lastComment['id'];
            }
        }

        $comments = $this->commentRepository->findWithTopicAndAuthor($ids);

        // fetch joining the roles above would repeat every comment body per role
        $authors = array_filter(array_map(static fn (Comment $comment) => $comment->getCreatedBy(), $comments));
        $this->userRepository->preloadRoles($authors);
    }

    public function getLastComment(Forum|Topic $subject): ?Comment
    {
        if ($subject instanceof Topic) {
            return $this->getLastCommentForTopic($subject);
        }

        /** @var User|null $user */
        $user = $this->security->getUser();
        $userId = (string)($user?->getId() ?? 'guest');
        $comment = $this->getLastCommentForForumTree($subject, $userId);
        return $comment !== null
            ? $this->commentRepository->find($comment['id'])
            : null;
    }

    private function getLastCommentForTopic(Topic $topic): ?Comment
    {
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
     * @throws InvalidArgumentException
     */
    private function getLastCommentForForumTree(Forum $forum, string $userId): ?array
    {
        return $this->cache->get("forumify.forum.{$forum->getId()}.last_comment.$userId", function (ItemInterface $item) use ($forum, $userId) {
            $item->tag([self::LAST_COMMENT_CACHE_TAG]);

            return $this->refreshLastCommentCache($forum, $userId);
        });
    }

    /**
     * @return array{id: int, createdAt: DateTime}|null
     */
    private function refreshLastCommentCache(Forum $forum, string $userId): ?array
    {
        $lastComments = $forum->getChildren()
            ->filter(fn (Forum $child) => $this->aclService->can('view', $child))
            ->map(fn (Forum $child) => $this->getLastCommentForForumTree($child, $userId));

        $lastComment = $this->getLastCommentPerForum()[$forum->getId()] ?? null;

        foreach ($lastComments as $maybeLast) {
            if ($maybeLast === null) {
                continue;
            }

            if ($lastComment === null) {
                $lastComment = $maybeLast;
                continue;
            }

            if ($maybeLast['createdAt'] > $lastComment['createdAt']) {
                $lastComment = $maybeLast;
            }
        }

        return $lastComment;
    }

    /**
     * @return array<int, array{id: int, createdAt: DateTime}>
     */
    private function getLastCommentPerForum(): array
    {
        /** @var User|null $user */
        $user = $this->security->getUser();

        return $this->lastCommentPerForum ??= $this->commentRepository->findLastCommentPerForum(
            $user,
            $this->forumVisibility->getTopicVisibilityPerForum(),
        );
    }

    public function reset(): void
    {
        $this->lastCommentPerForum = null;
    }
}
