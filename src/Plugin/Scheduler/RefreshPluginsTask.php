<?php

declare(strict_types=1);

namespace Forumify\Plugin\Scheduler;

use DateInterval;
use Forumify\Core\Scheduler\TaskInterface;

class RefreshPluginsTask implements TaskInterface
{
    public function getFrequency(): string|int|DateInterval
    {
        return '24 hours';
    }
}
