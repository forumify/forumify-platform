<?php

declare(strict_types=1);

namespace Forumify\Forum\Service;

use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Forumify\Core\Entity\User;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\Topic;
use Forumify\Forum\Repository\CommentRepository;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class LastCommentService
{
    public const LAST_COMMENT_CACHE_TAG = 'forumify.forum.last_comment';

    public function __construct(
        /**
         * @var TagAwareCacheInterface
         */
        private readonly CacheInterface $cache,
        private readonly Security $security,
        private readonly CommentRepository $commentRepository,
    ) {
    }

    public function clearCache(): void
    {
        $this->cache->invalidateTags([self::LAST_COMMENT_CACHE_TAG]);
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
        return $this->cache->get("forumify.forum.$forum.last_comment.$userId", function (ItemInterface $item) use ($forum, $userId) {
            $item->tag([self::LAST_COMMENT_CACHE_TAG]);

            return $this->refreshLastCommentCache($forum, $userId);
        });
    }

    private function refreshLastCommentCache(Forum $forum, string $userId)
    {
        $lastComments = $forum->getChildren()
            ->filter(fn (Forum $child) => $this->security->isGranted(VoterAttribute::ACL->value, ['entity' => $child, 'permission' => 'view']))
            ->map(fn (Forum $child) => $this->getLastCommentForForumTree($child, $userId));

        $onlyShowOwnTopics = $forum->getDisplaySettings()->isOnlyShowOwnTopics();
        $canViewAll = !$onlyShowOwnTopics || $this->security->isGranted(VoterAttribute::ACL->value, ['entity' => $forum, 'permission' => 'show_all_topics']);
        $canViewHidden = $this->security->isGranted(VoterAttribute::Moderator->value, $forum);

        $lastCommentEntity = $this->commentRepository->findLastCommentForForumAndUserId($forum, $userId, $canViewAll, $canViewHidden);
        $lastComment = $lastCommentEntity !== null
            ? ['id' => $lastCommentEntity->getId(), 'createdAt' => $lastCommentEntity->getCreatedAt()]
            : null;

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
}
