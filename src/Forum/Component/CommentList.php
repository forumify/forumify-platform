<?php

declare(strict_types=1);

namespace Forumify\Forum\Component;

use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Component\List\AbstractDoctrineList;
use Forumify\Forum\Entity\Topic;
use Forumify\Forum\Repository\CommentRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

#[AsLiveComponent(name: 'CommentList', template: '@Forumify/frontend/components/comment_list.html.twig')]
class CommentList extends AbstractDoctrineList
{
    #[LiveProp]
    public Topic $topic;

    public function __construct(private readonly CommentRepository $commentRepository)
    {
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->commentRepository
            ->createQueryBuilder('c')
            ->where('c.topic = :topic')
            ->orderBy('c.createdAt', 'ASC')
            ->setParameter('topic', $this->topic);
    }

    protected function getCount(): int
    {
        return $this->commentRepository->count(['topic' => $this->topic]);
    }
}
