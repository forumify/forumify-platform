<?php

declare(strict_types=1);

namespace Forumify\Forum\Repository;

use Forumify\Core\Repository\AbstractRepository;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\TopicImage;

class TopicImageRepository extends AbstractRepository
{
    public static function getEntityClass(): string
    {
        return TopicImage::class;
    }

    /**
     * @return array<TopicImage>
     */
    public function findByForum(int|Forum $forum): array
    {
        $qb = $this->createQueryBuilder('ti');
        $qb
            ->join('ti.topic', 't')
            ->join('t.forum', 'f')
            ->where('f = :forum')
            ->orderBy('ti.createdAt', 'DESC')
            ->setParameter('forum', $forum)
        ;

        return $qb->getQuery()->getResult();
    }
}
