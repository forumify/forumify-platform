<?php

declare(strict_types=1);

namespace Forumify\Automation\Condition;

use Forumify\Automation\AutomationComponentInterface;
use Forumify\Automation\Entity\Automation;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('forumify.automation.condition')]
interface ConditionInterface extends AutomationComponentInterface
{
    /**
     * @param Automation $automation
     * @param array<string, mixed>|null $payload
     * @return bool
     */
    public function evaluate(Automation $automation, ?array $payload): bool;
}
