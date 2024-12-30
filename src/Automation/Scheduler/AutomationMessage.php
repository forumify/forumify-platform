<?php

declare(strict_types=1);

namespace Forumify\Automation\Scheduler;

use Forumify\Core\Messenger\AsyncMessageInterface;

class AutomationMessage implements AsyncMessageInterface
{
    public function __construct(
        public readonly int $automationId,
        public readonly ?array $payload = [],
    ) {
    }
}
