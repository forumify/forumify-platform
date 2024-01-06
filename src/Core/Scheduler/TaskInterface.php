<?php
declare(strict_types=1);

namespace Forumify\Core\Scheduler;

use DateInterval;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Scheduler\RecurringMessage;

#[AutoconfigureTag('forumify.task')]
interface TaskInterface
{
    /**
     * Configure the frequency of the task
     *
     * @see RecurringMessage::every()
     */
    public function getFrequency(): string|int|DateInterval;
}
