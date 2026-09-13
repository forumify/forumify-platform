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
                ->setParameter('user', $user)
                ->setParameter('subject', $subject)
                ->setParameter('subjectId', $subjectId)
                ->getQuery()
                ->getSingleScalarResult();
            return $count > 0;
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
     * @param array<int> $subjectIds
     */
    public function markAllRead(User $user, string $subject, array $subjectIds): void
    {
        if (empty($subjectIds)) {
            return;
        }

        $alreadyRead = $this->findReadSubjectIds($user, $subject, $subjectIds);
        foreach (array_diff($subjectIds, $alreadyRead) as $subjectId) {
            $this->save(new ReadMarker($user, $subject, $subjectId), false);
        }
        $this->flush();
    }

    /**
     * @param array<int> $subjectIds
     * @return array<int>
     */
    public function findReadSubjectIds(User $user, string $subject, array $subjectIds): array
    {
        if (empty($subjectIds)) {
            return [];
        }

        $readSubjectIds = $this->createQueryBuilder('rm')
            ->select('rm.subjectId')
            ->where('rm.user = :user')
            ->andWhere('rm.subject = :subject')
            ->andWhere('rm.subjectId IN (:subjectIds)')
            ->setParameter('user', $user)
            ->setParameter('subject', $subject)
            ->setParameter('subjectIds', $subjectIds)
            ->getQuery()
            ->getSingleColumnResult();

        return array_map(intval(...), $readSubjectIds);
    }

    public function unread(string $subject, int $subjectId): void
    {
        $this->createQueryBuilder('rm')
            ->delete(ReadMarker::class, 'rm')
            ->where('rm.subject = :subject')
            ->andWhere('rm.subjectId = :subjectId')
            ->setParameter('subject', $subject)
            ->setParameter('subjectId', $subjectId)
            ->getQuery()
            ->execute();
    }
}
