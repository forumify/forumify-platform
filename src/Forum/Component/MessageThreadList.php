<?php

declare(strict_types=1);

namespace Forumify\Forum\Component;

use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Component\AbstractList;
use Forumify\Core\Entity\User;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\MessageThread;
use Forumify\Forum\Repository\MessageRepository;
use Forumify\Forum\Repository\MessageThreadRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(template: '@Forumify/components/message_thread_list.html.twig', name: 'MessageThreadList')]
class MessageThreadList extends AbstractList
{
    use DefaultActionTrait;

    #[LiveProp]
    public ?int $selectedThreadId = null;
    private ?MessageThread $selectedThread = null;

    public function __construct(
        private readonly MessageThreadRepository $messageThreadRepository,
        private readonly MessageRepository $messageRepository,
        private readonly Security $security,
        private readonly FormFactoryInterface $formFactory,
    ) {
    }

    #[LiveAction]
    public function setSelectedThread(#[LiveArg] int $threadId): void
    {
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

    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->messageThreadRepository
            ->createQueryBuilder('mt')
            ->join('mt.participants', 'p')
            ->where('p.id = (:userId)')
            ->orderBy('mt.createdAt', 'DESC')
            ->setParameter('userId', $this->getUser());
    }

    protected function getCount(): int
    {
        return $this->messageThreadRepository
            ->createQueryBuilder('mt')
            ->select('COUNT(mt.id)')
            ->join('mt.participants', 'p')
            ->where('p.id = (:userId)')
            ->setParameter('userId', $this->getUser())
            ->getFirstResult();
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
