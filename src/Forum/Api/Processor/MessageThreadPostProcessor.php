<?php

declare(strict_types=1);

namespace Forumify\Forum\Api\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Forumify\Forum\Entity\MessageThread;
use Forumify\Forum\Form\NewMessageThread;
use Forumify\Forum\Service\MessageService;

/**
 * @implements ProcessorInterface<NewMessageThread, MessageThread>
 */
class MessageThreadPostProcessor implements ProcessorInterface
{
    public function __construct(private readonly MessageService $messageService)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        return $this->messageService->createThread($data);
    }
}
