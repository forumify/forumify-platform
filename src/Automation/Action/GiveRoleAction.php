<?php

declare(strict_types=1);

namespace Forumify\Automation\Action;

use Forumify\Automation\Entity\Automation;
use Forumify\Automation\Form\GiveRoleActionType;
use Forumify\Automation\Service\UserExpressionResolver;
use Forumify\Core\Notification\ContextSerializer;
use Forumify\Core\Repository\UserRepository;

class GiveRoleAction implements ActionInterface
{
    public function __construct(
        private readonly ContextSerializer $contextSerializer,
        private readonly UserExpressionResolver $userExpressionResolver,
        private readonly UserRepository $userRepository,
    ) {
    }

    public static function getType(): string
    {
        return 'Give Role';
    }

    /**
     * @param Automation $automation
     * @param array<string, mixed>|null $payload
     * @return void
     */
    public function run(Automation $automation, ?array $payload): void
    {
        [
            'role' => $role,
            'recipient' => $recipientExpr,
        ] = $this->contextSerializer->deserialize($automation->getActionArguments());

        $recipient = $this->userExpressionResolver->resolve($recipientExpr, $payload);
        $recipient = reset($recipient);
        if ($recipient === false) {
            return;
        }

        if ($recipient->getRoleEntities()->contains($role)) {
            return;
        }

        $recipient->getRoleEntities()->add($role);
        $this->userRepository->save($recipient);
    }

    public function getPayloadFormType(): ?string
    {
        return GiveRoleActionType::class;
    }
}
