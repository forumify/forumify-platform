<?php

declare(strict_types=1);

namespace Forumify\Automation\Scheduler;

use Forumify\Automation\Action\ActionInterface;
use Forumify\Automation\Entity\Automation;
use Forumify\Automation\Repository\AutomationRepository;
use Forumify\Core\Notification\ContextSerializer;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: AutomationMessage::class)]
class AutomationMessageHandler
{
    /**
     * @param iterable<ActionInterface> $actions
     */
    public function __construct(
        #[AutowireIterator('forumify.automation.action', defaultIndexMethod: 'getType')]
        private readonly iterable $actions,
        private readonly AutomationRepository $automationRepository,
        private readonly ContextSerializer $contextSerializer,
    ) {
    }

    public function __invoke(AutomationMessage $message): void
    {
        /** @var Automation|null $automation */
        $automation = $this->automationRepository->find($message->automationId);
        if ($automation === null) {
            return;
        }

        $payload = $message->payload !== null
            ? $this->contextSerializer->deserialize($message->payload)
            : null;

        /** @var array<string, ActionInterface> $actions */
        $actions = iterator_to_array($this->actions);
        $action = $automation->getAction();
        if (isset($actions[$action])) {
            $actions[$action]->run($automation, $payload);
        }
    }
}
