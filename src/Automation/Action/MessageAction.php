<?php

declare(strict_types=1);

namespace Forumify\Automation\Action;

use Doctrine\Common\Collections\ArrayCollection;
use Forumify\Automation\Entity\Automation;
use Forumify\Automation\Form\MessageActionType;
use Forumify\Automation\Service\UserExpressionResolver;
use Forumify\Forum\Form\NewMessageThread;
use Forumify\Forum\Service\MessageService;
use Twig\Environment;

class MessageAction implements ActionInterface
{
    public function __construct(
        private readonly UserExpressionResolver $userExpressionResolver,
        private readonly MessageService $messageService,
        private readonly Environment $twig,
    ) {
    }

    public static function getType(): string
    {
        return 'Send Message';
    }

    public function getPayloadFormType(): ?string
    {
        return MessageActionType::class;
    }

    public function run(Automation $automation, ?array $payload): void
    {
        [
            'recipients' => $recipientExpr,
            'title' => $title,
            'message' => $message,
        ] = $automation->getActionArguments();

        $participants = $this->userExpressionResolver->resolve($recipientExpr, $payload);
        if (empty($participants)) {
            return;
        }

        $title = $this->twig->createTemplate($title)->render($payload ?? []);
        $message = $this->twig->createTemplate($message)->render($payload ?? []);

        $thread = new NewMessageThread();
        $thread->setTitle($title);
        $thread->setMessage($message);
        $thread->setParticipants(new ArrayCollection($participants));

        $this->messageService->createThread($thread);
    }
}
