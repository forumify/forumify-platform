<?php

declare(strict_types=1);

namespace Forumify\Core\Scheduler;

use DateInterval;

class RemoveUserTask implements TaskInterface
{
    public function getFrequency(): string|int|DateInterval
    {
        return '4 hours';
    }
}
