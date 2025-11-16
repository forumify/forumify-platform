<?php

declare(strict_types=1);

namespace Forumify\Forum\Component;

use Doctrine\ORM\QueryBuilder;
use Forumify\Core\Component\List\AbstractDoctrineList;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\Message;
use Forumify\Forum\Entity\MessageThread;
use Forumify\Forum\Form\MessageReplyType;
use Forumify\Forum\Repository\MessageThreadRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

/**
 * @extends AbstractDoctrineList<Message>
 */
#[AsLiveComponent(name: 'MessageList', template: '@Forumify/frontend/components/messenger/message_list.html.twig')]
class MessageList extends AbstractDoctrineList
{
    use DefaultActionTrait;

    #[LiveProp(updateFromParent: true)]
    public int $threadId;

    private ?MessageThread $thread = null;
    private ?FormView $replyForm = null;

    public function __construct(
        private readonly MessageThreadRepository $messageThreadRepository,
        private readonly FormFactoryInterface $formFactory,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Security $security,
    ) {
    }

    protected function getEntityClass(): string
    {
        return Message::class;
    }

    protected function getQuery(): QueryBuilder
    {
        return parent::getQuery()
            ->where('e.thread = :thread')
            ->orderBy('e.createdAt', 'ASC')
            ->setParameter('thread', $this->getThread());
    }

    public function getThread(): ?MessageThread
    {
        if ($this->thread !== null) {
            return $this->thread;
        }

        $thread = $this->messageThreadRepository->find($this->threadId);
        if ($thread === null) {
            throw new NotFoundHttpException("Unable to find MessageThread with id {$this->threadId}");
        }

        if (!$this->security->isGranted(VoterAttribute::MessageThreadView->value, $thread)) {
            throw new AccessDeniedException('You are not allowed to view this thread');
        }

        $this->thread = $thread;
        return $this->thread;
    }

    public function getReplyForm(): FormView
    {
        if ($this->replyForm !== null) {
            return $this->replyForm;
        }

        $action = $this->urlGenerator->generate('forumify_forum_messenger_reply', ['id' => $this->getThread()?->getId()]);
        $this->replyForm = $this->formFactory
            ->createBuilder(MessageReplyType::class, null, ['thread' => $this->getThread()])
            ->setAction($action)
            ->getForm()
            ->createView();

        return $this->replyForm;
    }
}
