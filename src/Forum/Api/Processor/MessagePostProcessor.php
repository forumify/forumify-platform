<?php

declare(strict_types=1);

namespace Forumify\Forum\Api\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\Message;
use Forumify\Forum\Form\MessageReply;
use Forumify\Forum\Repository\MessageThreadRepository;
use Forumify\Forum\Service\MessageService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProcessorInterface<MessageReply, Message>
 */
class MessagePostProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly MessageThreadRepository $messageThreadRepository,
        private readonly Security $security,
        private readonly MessageService $messageService,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $thread = $this->messageThreadRepository->find($uriVariables['threadId']);
        if ($thread === null) {
            throw new NotFoundHttpException("Thread with id {$uriVariables['threadId']} does not exist.");
        }

        if (!$this->security->isGranted(VoterAttribute::MessageThreadReply->value, $thread)) {
            throw new AccessDeniedHttpException();
        }

        return $this->messageService->replyToThread($thread, $data);
    }
}
