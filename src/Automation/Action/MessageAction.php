<?php

declare(strict_types=1);

namespace Forumify\Automation\Action;

use Doctrine\Common\Collections\ArrayCollection;
use Forumify\Automation\Entity\Automation;
use Forumify\Automation\Form\MessageActionType;
use Forumify\Core\Entity\User;
use Forumify\Core\Repository\UserRepository;
use Forumify\Forum\Form\NewMessageThread;
use Forumify\Forum\Service\MessageService;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Twig\Environment;

class MessageAction implements ActionInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly MessageService $messageService,
        private readonly Environment $twig,
    ) {
    }

    public static function getType(): string
    {
        return 'Send Message';
    }

    public function run(Automation $automation, ?array $payload): void
    {
        [
            'recipients' => $recipientExpr,
            'title' => $title,
            'message' => $message,
        ] = $automation->getActionArguments();

        $participants = $this->getParticipants($recipientExpr, $payload);
        if ($participants->isEmpty()) {
            return;
        }

        $title = $this->twig->createTemplate($title)->render($payload ?? []);
        $message = $this->twig->createTemplate($message)->render($payload ?? []);

        $thread = new NewMessageThread();
        $thread->setTitle($title);
        $thread->setMessage($message);
        $thread->setParticipants($participants);

        $this->messageService->createThread($thread);
    }

    public function getPayloadFormType(): ?string
    {
        return MessageActionType::class;
    }

    private function getParticipants(string $recipientExpr, ?array $payload): ArrayCollection
    {
        $participants = new ArrayCollection();

        $expressionLanguage = new ExpressionLanguage();
        $recipients = $expressionLanguage->evaluate($recipientExpr, $payload ?? []);
        if (!is_array($recipients)) {
            return $participants;
        }

        foreach ($recipients as $recipient) {
            $this->addParticipant($participants, $recipient);
        }

        return $participants;
    }

    private function addParticipant(ArrayCollection $participants, mixed $identifier): void
    {
        if ($identifier instanceof User) {
            $participants->add($identifier);
            return;
        }

        if (is_numeric($identifier)) {
            $id = (int)$identifier;
            $user = $this->userRepository->find($id);
            if ($user !== null) {
                $participants->add($user);
            }
            return;
        }

        if (is_string($identifier)) {
            $user = $this->userRepository->findOneBy(['username' => $identifier]);
            if ($user !== null) {
                $participants->add($user);
            }
        }
    }
}
