<?php

declare(strict_types=1);

namespace Forumify\Automation\Action;

use Forumify\Automation\AutomationComponentInterface;
use Forumify\Automation\Entity\Automation;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('forumify.automation.action')]
interface ActionInterface extends AutomationComponentInterface
{
    /**
     * @param Automation $automation
     * @param array<string, mixed>|null $payload
     * @return void
     */
    public function run(Automation $automation, ?array $payload): void;
}
