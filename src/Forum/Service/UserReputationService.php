<?php

declare(strict_types=1);

namespace Forumify\Forum\Service;

use DateInterval;
use Forumify\Core\Entity\User;
use Forumify\Forum\Repository\CommentReactionRepository;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Forumify\Forum\Entity\CommentReaction;
use Forumify\Forum\Entity\Reaction;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[AsEntityListener(event: Events::postPersist, method: 'clearCommentCache', entity: CommentReaction::class)]
#[AsEntityListener(event: Events::postRemove, method: 'clearCommentCache', entity: CommentReaction::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'clearReactionCache', entity: Reaction::class)]
#[AsEntityListener(event: Events::postRemove, method: 'clearReactionCache', entity: Reaction::class)]
class UserReputationService
{
    /**
     * @param TagAwareCacheInterface $cache
     */
    public function __construct(
        private readonly CommentReactionRepository $commentReactionRepository,
        private readonly CacheInterface $cache,
    ) {
    }

    public function getReputation(User $user): int
    {
        return $this->cache->get($this->getCacheKey($user), function (ItemInterface $item) use ($user) {
            $item->expiresAfter(new DateInterval('P1D'));
            $item->tag([$this->getCacheKey()]);

            return (int)$this->commentReactionRepository
                ->createQueryBuilder('cr')
                ->select('SUM(r.reputation)')
                ->join('cr.reaction', 'r')
                ->join('cr.comment', 'c')
                ->where('c.createdBy = :user')
                ->andWhere('cr.user != :user')
                ->setParameter('user', $user)
                ->getQuery()
                ->getSingleScalarResult()
            ;
        });
    }

    public function clearCommentCache(CommentReaction $commentReaction): void
    {
        $user = $commentReaction->getComment()->getCreatedBy();
        if ($user !== null) {
            $this->cache->delete($this->getCacheKey($user));
        }
    }

    public function clearReactionCache(): void
    {
        $this->cache->invalidateTags([$this->getCacheKey()]);
    }

    private function getCacheKey(?User $user = null): string
    {
        $prefix = 'forumify.forum.user_reputation';
        if ($user === null) {
            return $prefix;
        }
        return $prefix . '_' . $user->getId();
    }
}
