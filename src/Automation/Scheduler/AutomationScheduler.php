<?php

declare(strict_types=1);

namespace Forumify\Automation\Scheduler;

use Forumify\Automation\Condition\ConditionInterface;
use Forumify\Automation\Entity\Automation;
use Forumify\Core\Notification\NotificationContextSerializer;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class AutomationScheduler
{
    /**
     * @param iterable<ConditionInterface> $conditions
     */
    public function __construct(
        #[AutowireIterator('forumify.automation.condition', defaultIndexMethod: 'getType')]
        private readonly iterable $conditions,
        private readonly MessageBusInterface $messageBus,
        private readonly NotificationContextSerializer $contextSerializer,
    ) {
    }

    public function schedule(Automation $automation, ?array $payload): void
    {
        if (!$this->shouldSchedule($automation, $payload)) {
            return;
        }

        if ($payload !== null) {
            $payload = $this->contextSerializer->serialize($payload);
        }

        $message = new AutomationMessage($automation->getId(), $payload);
        $this->messageBus->dispatch($message, [new DelayStamp(2000)]);
    }

    private function shouldSchedule(Automation $automation, ?array $payload): bool
    {
        /** @var array<string, ConditionInterface> $conditions */
        $conditions = iterator_to_array($this->conditions);
        $condition = $automation->getCondition();
        if ($condition !== null && isset($conditions[$condition])) {
            $shouldRun = $conditions[$condition]->evaluate($automation, $payload);
            if (!$shouldRun) {
                return false;
            }
        }

        return true;
    }
}
