<?php

declare(strict_types=1);

namespace Forumify\Forum\Component;

use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Component\List\AbstractDoctrineList;
use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Entity\Topic;
use Forumify\Forum\Repository\CommentRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Metadata\UrlMapping;
use Symfony\UX\TwigComponent\Attribute\PostMount;

/**
 * @extends AbstractDoctrineList<Comment>
 */
#[AsLiveComponent(name: 'CommentList', template: '@Forumify/frontend/components/comment_list.html.twig')]
class CommentList extends AbstractDoctrineList
{
    #[LiveProp]
    public Topic $topic;

    #[LiveProp(url: new UrlMapping('comment'))]
    public ?int $selectedCommentId = null;

    public function __construct(private readonly CommentRepository $commentRepository)
    {
    }

    #[PostMount]
    public function setPageForComment(): void
    {
        if (!$this->selectedCommentId) {
            return;
        }

        $comment = $this->commentRepository->find($this->selectedCommentId);
        if ($comment === null) {
            return;
        }

        $position = (int)parent::getQuery()
            ->select('COUNT(e.id)')
            ->where('e.topic = :topic')
            ->andWhere('e.createdAt < :createdAt')
            ->andWhere('e.id < :id')
            ->setParameter('topic', $this->topic)
            ->setParameter('createdAt', $comment->getCreatedAt())
            ->setParameter('id', $comment->getId())
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $this->page = intdiv($position, $this->limit) + 1;
    }

    #[LiveAction]
    public function setPage(#[LiveArg] int $page): void
    {
        $this->page = $page;
        $this->selectedCommentId = null;
    }

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
            ->addOrderBy('e.id', 'ASC')
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
