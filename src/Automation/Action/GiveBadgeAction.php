<?php

declare(strict_types=1);

namespace Forumify\Automation\Action;

use Forumify\Automation\Entity\Automation;
use Forumify\Automation\Form\GiveBadgeActionType;
use Forumify\Automation\Service\UserExpressionResolver;
use Forumify\Core\Notification\ContextSerializer;
use Forumify\Core\Repository\UserRepository;

class GiveBadgeAction implements ActionInterface
{
    public function __construct(
        private readonly ContextSerializer $contextSerializer,
        private readonly UserExpressionResolver $userExpressionResolver,
        private readonly UserRepository $userRepository,
    ) {
    }

    public static function getType(): string
    {
        return 'Give Badge';
    }

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

        $recipient->getBadges()->add($badge);
        $this->userRepository->save($recipient);
    }

    public function getPayloadFormType(): ?string
    {
        return GiveBadgeActionType::class;
    }
}
