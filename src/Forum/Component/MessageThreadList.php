<?php

declare(strict_types=1);

namespace Forumify\Forum\Component;

use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Component\List\AbstractDoctrineList;
use Forumify\Core\Entity\ReadMarker;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\ReadMarkerRepository;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\MessageThread;
use Forumify\Forum\Repository\MessageThreadRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(name: 'MessageThreadList', template: '@Forumify/frontend/components/message_thread_list.html.twig')]
class MessageThreadList extends AbstractDoctrineList
{
    use DefaultActionTrait;

    #[LiveProp]
    public ?int $selectedThreadId = null;
    private ?MessageThread $selectedThread = null;

    public function __construct(
        private readonly MessageThreadRepository $messageThreadRepository,
        private readonly Security $security,
        private readonly ReadMarkerRepository $readMarkerRepository,
    ) {
    }

    #[LiveAction]
    public function setSelectedThread(#[LiveArg] ?int $threadId = null): void
    {
        if ($threadId === null) {
            $this->selectedThreadId = null;
            $this->selectedThread = null;
            return;
        }

        $user = $this->getUser();
        if (!$this->readMarkerRepository->isRead($user, MessageThread::class, $threadId)) {
            $this->readMarkerRepository->save(new ReadMarker($user, MessageThread::class, $threadId));
        }

        $this->selectedThreadId = $threadId;
    }

    public function getSelectedThread(): ?MessageThread
    {
        if ($this->selectedThreadId === null) {
            return null;
        }

        if ($this->selectedThread !== null) {
            return $this->selectedThread;
        }

        $thread = $this->messageThreadRepository->find($this->selectedThreadId);
        if ($thread === null) {
            throw new NotFoundHttpException();
        }

        if (!$this->security->isGranted(VoterAttribute::MessageThreadView->value, $thread)) {
            throw new AccessDeniedException();
        }

        $this->selectedThread = $thread;
        return $this->selectedThread;
    }

    protected function getEntityClass(): string
    {
        return MessageThread::class;
    }

    protected function getQuery(): QueryBuilder
    {
        return parent::getQuery()
            ->select('e, MAX(m.createdAt) AS HIDDEN maxCreatedAt')
            ->leftJoin('e.messages', 'm')
            ->join('e.participants', 'p')
            ->where('p = (:user)')
            ->setParameter('user', $this->getUser())
            ->groupBy('e.id')
            ->orderBy('maxCreatedAt', 'DESC')
        ;
    }

    protected function getTotalCount(): int
    {
        return parent::getQuery()
            ->select('COUNT(e.id)')
            ->join('e.participants', 'p')
            ->where('p = (:user)')
            ->setParameter('user', $this->getUser())
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function getUser(): User
    {
        /** @var User|null $user */
        $user = $this->security->getUser();
        if ($user === null) {
            throw new AccessDeniedException();
        }

        return $user;
    }
}
