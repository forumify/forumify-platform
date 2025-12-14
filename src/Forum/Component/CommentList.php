<?php

declare(strict_types=1);

namespace Forumify\Forum\Component;

use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Component\List\AbstractDoctrineList;
use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Entity\Topic;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

/**
 * @extends AbstractDoctrineList<Comment>
 */
#[AsLiveComponent(name: 'CommentList', template: '@Forumify/frontend/components/comment_list.html.twig')]
class CommentList extends AbstractDoctrineList
{
    #[LiveProp]
    public Topic $topic;

    protected function getEntityClass(): string
    {
        return Comment::class;
    }

    protected function getQuery(): QueryBuilder
    {
        return parent::getQuery()
            ->innerJoin('e.topic', 't')
            ->leftJoin('t.firstComment', 'tfc')
            ->leftJoin('t.answer', 'ta')
            ->where('e.topic = :topic')
            ->orderBy('CASE
                WHEN e.id = tfc.id THEN 0
                WHEN e.id = ta.id THEN 1
                ELSE 2
                END', 'ASC')
            ->addOrderBy('e.createdAt', 'ASC')
            ->setParameter('topic', $this->topic);
    }

    protected function getTotalCount(): int
    {
        return (int)parent::getQuery()
            ->select('COUNT(e.id)')
            ->where('e.topic = :topic')
            ->setParameter('topic', $this->topic)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
