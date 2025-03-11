<?php

declare(strict_types=1);

namespace Forumify\Automation\Trigger;

use Forumify\Automation\Entity\Automation;
use Forumify\Automation\Form\DoctrineTriggerType;
use Forumify\Automation\Repository\AutomationRepository;
use Forumify\Automation\Scheduler\AutomationScheduler;

abstract class AbstractDoctrineTrigger
{
    /**
     * @var null|array<Automation>
     */
    private ?array $automations = null;

    public function __construct(
        private readonly AutomationRepository $automationRepository,
        private readonly AutomationScheduler $automationScheduler,
    ) {
    }

    abstract public static function getType(): string;

    public function getPayloadFormType(): ?string
    {
        return DoctrineTriggerType::class;
    }

    protected function trigger(object $entity): void
    {
        $this->automations ??= $this->automationRepository->findByTriggerType(static::getType());
        foreach ($this->automations as $automation) {
            $entities = $automation->getTriggerArguments()['entities'] ?? [];
            if (empty($entities) || in_array(get_class($entity), $entities, true)) {
                $this->automationScheduler->schedule($automation, ['entity' => $entity]);
            }
        }
    }
}
