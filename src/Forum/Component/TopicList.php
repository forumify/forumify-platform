<?php

declare(strict_types=1);

namespace Forumify\Forum\Component;

use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Component\List\AbstractDoctrineList;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Repository\TopicRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

#[AsLiveComponent(name: 'TopicList', template: '@Forumify/frontend/components/topic_list.html.twig')]
class TopicList extends AbstractDoctrineList
{
    #[LiveProp]
    public Forum $forum;

    public function __construct(
        private readonly TopicRepository $topicRepository,
        private readonly Security $security,
    ) {
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        $qb = $this->topicRepository
            ->createQueryBuilder('t')
            ->where('t.forum = :forum')
            ->join('t.lastComment', 'lc')
            ->orderBy('t.pinned', 'DESC')
            ->addOrderBy('lc.createdAt', 'DESC')
            ->setParameter('forum', $this->forum);

        $canViewHidden = $this->security->isGranted(VoterAttribute::Moderator->value);
        if (!$canViewHidden) {
            $qb->andWhere('t.hidden = 0');
        }

        return $qb;
    }

    protected function getCount(): int
    {
        return $this->topicRepository->count(['forum' => $this->forum]);
    }
}
