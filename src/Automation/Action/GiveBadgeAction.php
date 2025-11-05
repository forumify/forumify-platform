<?php

declare(strict_types=1);

namespace Forumify\Automation\Action;

use Forumify\Automation\Entity\Automation;
use Forumify\Automation\Form\GiveBadgeActionType;
use Forumify\Automation\Service\UserExpressionResolver;
use Forumify\Core\Entity\Notification;
use Forumify\Core\Notification\ContextSerializer;
use Forumify\Core\Notification\NotificationService;
use Forumify\Core\Repository\UserRepository;
use Forumify\Forum\Notification\NewBadgeNotificationType;

class GiveBadgeAction implements ActionInterface
{
    public function __construct(
        private readonly ContextSerializer $contextSerializer,
        private readonly UserExpressionResolver $userExpressionResolver,
        private readonly UserRepository $userRepository,
        private readonly NotificationService $notificationService,
    ) {
    }

    public static function getType(): string
    {
        return 'Give Badge';
    }

    /**
     * @param Automation $automation
     * @param array<string, mixed>|null $payload
     * @return void
     */
    public function run(Automation $automation, ?array $payload): void
    {
        [
            'badge' => $badge,
            'recipient' => $recipientExpr,
        ] = $this->contextSerializer->deserialize($automation->getActionArguments());

        $recipient = $this->userExpressionResolver->resolve($recipientExpr, $payload);
        $recipient = reset($recipient);
        if ($recipient === false) {
            return;
        }

        if ($recipient->getBadges()->contains($badge)) {
            return;
        }

        $recipient->getBadges()->add($badge);
        $this->userRepository->save($recipient);

        $this->notificationService->sendNotification(new Notification(
            NewBadgeNotificationType::TYPE,
            $recipient,
            ['badge' => $badge],
        ));
    }

    public function getPayloadFormType(): ?string
    {
        return GiveBadgeActionType::class;
    }
}
