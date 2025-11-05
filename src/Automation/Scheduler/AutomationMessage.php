<?php

declare(strict_types=1);

namespace Forumify\Automation\Scheduler;

use Forumify\Core\Messenger\AsyncMessageInterface;

class AutomationMessage implements AsyncMessageInterface
{
    /**
     * @param int $automationId
     * @param array<string, mixed>|null $payload
     */
    public function __construct(
        public readonly int $automationId,
        public readonly ?array $payload = [],
    ) {
    }
}
