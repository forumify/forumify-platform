<?php

declare(strict_types=1);

namespace Forumify\Automation\Trigger;

use Forumify\Automation\AutomationComponentInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('forumify.automation.trigger')]
interface TriggerInterface extends AutomationComponentInterface
{
}
