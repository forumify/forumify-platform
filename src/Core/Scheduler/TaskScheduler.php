<?php

declare(strict_types=1);

namespace Forumify\Core\Scheduler;

use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule('task')]
class TaskScheduler implements ScheduleProviderInterface
{
    private ?Schedule $schedule = null;
    /** @var array<TaskInterface> */
    private array $tasks;

    public function __construct(#[TaggedIterator('forumify.task')] iterable $tasks)
    {
        $this->tasks = iterator_to_array($tasks);
    }

    public function getSchedule(): Schedule
    {
        if ($this->schedule !== null) {
            return $this->schedule;
        }

        $this->schedule = (new Schedule());
        foreach ($this->tasks as $task) {
            $this->schedule->add(RecurringMessage::every($task->getFrequency(), new (get_class($task))));
        }

        return $this->schedule;
    }
}
