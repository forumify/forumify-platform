<?php

declare(strict_types=1);

namespace Forumify\Core\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Forumify\Core\Entity\ReadMarker;
use Forumify\Core\Entity\User;

/**
 * @extends AbstractRepository<ReadMarker>
 */
class ReadMarkerRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return ReadMarker::class;
    }

    public function isRead(User $user, string $subject, int $subjectId): bool
    {
        try {
            $count = $this->createQueryBuilder('rm')
                ->select('COUNT(rm.subjectId)')
                ->where('rm.user = :user')
                ->andWhere('rm.subject = :subject')
                ->andWhere('rm.subjectId = :subjectId')
                ->setParameters([
                    'user' => $user,
                    'subject' => $subject,
                    'subjectId' => $subjectId,
                ])
                ->getQuery()
                ->getSingleScalarResult();
            return $count > 0;
        } catch (NoResultException|NonUniqueResultException) {
            return false;
        }
    }

    /**
     * @param User $user
     * @param string $subject
     * @param int[] $subjectIds
     * @return bool
     */
    public function areAllRead(User $user, string $subject, array $subjectIds): bool
    {
        try {
            $count = $this->createQueryBuilder('rm')
                ->select('COUNT(rm.subjectId)')
                ->where('rm.user = :user')
                ->andWhere('rm.subject = :subject')
                ->andWhere('rm.subjectId IN (:subjectIds)')
                ->setParameters([
                    'user' => $user,
                    'subject' => $subject,
                    'subjectIds' => $subjectIds,
                ])
                ->getQuery()
                ->getSingleScalarResult();

            return $count === count($subjectIds);
        } catch (NoResultException|NonUniqueResultException) {
            return false;
        }
    }

    public function read(User $user, string $subject, int $subjectId, bool $flush = true): void
    {
        if ($this->isRead($user, $subject, $subjectId)) {
            return;
        }

        $marker = new ReadMarker($user, $subject, $subjectId);
        $this->save($marker, $flush);
    }

    /**
     * @param User $user.
     * @param string $subject.
     * @param int[] $subjectIds.
     */
    public function markAllRead(User $user, string $subject, array $subjectIds): void
    {
        foreach ($subjectIds as $subjectId) {
            $this->read($user, $subject, $subjectId, false);
        }
        $this->flush();
    }

    public function unread(string $subject, int $subjectId): void
    {
        $this->createQueryBuilder('rm')
            ->delete(ReadMarker::class, 'rm')
            ->where('rm.subject = :subject')
            ->andWhere('rm.subjectId = :subjectId')
            ->getQuery()
            ->execute([
                'subject' => $subject,
                'subjectId' => $subjectId,
            ]);
    }
}
