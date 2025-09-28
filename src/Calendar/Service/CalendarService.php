<?php

declare(strict_types=1);

namespace Forumify\Calendar\Service;

use DateInterval;
use DateTime;
use DateTimeZone;
use Forumify\Calendar\Entity\Calendar;
use Forumify\Calendar\Entity\CalendarEvent;
use Forumify\Calendar\Repository\CalendarEventRepository;

class CalendarService
{
    private ?array $eventMemo = null;

    public function __construct(
        private readonly CalendarEventRepository $calendarEventRepository,
    ) {
    }

    /**
     * @return array<string, array<CalendarEvent>>
     */
    public function getAllEvents(\DateTime $date, ?Calendar $calendar): array
    {
        if ($this->eventMemo !== null) {
            return $this->eventMemo;
        }
        $allEvents = $this->calendarEventRepository->findByDateAndCalendar($date, $calendar);
        array_walk($allEvents, $this->setTimezone($date->getTimezone()));


        $start = clone $date;
        $start->modify('first day of previous month');

        $end = clone $date;
        $end->modify('last day of next month');

        $this->eventMemo = [];
        foreach ($allEvents as $event) {
            $this->processEvent($event, $start, $end);
        }

        return $this->eventMemo;
    }

    private function setTimezone(DateTimeZone $timezone): callable
    {
        return fn (CalendarEvent $event) => $event->getStart()->setTimezone($timezone);
    }

    private function processEvent(CalendarEvent $event, DateTime $start, DateTime $end): void
    {
        $eventDate = clone $event->getStart();
        if ($event->getRepeat() === null) {
            $this->eventMemo[$eventDate->format('Y-m-d')][] = $event;
            return;
        }

        $repeatEnd = $event->getRepeatEnd();
        if ($repeatEnd !== null) {
            $repeatEnd->setTimezone($start->getTimezone());
            $repeatEnd->setTime(23, 59, 59);

            $end = $repeatEnd;
        }

        while ($eventDate < $end) {
            if ($eventDate > $start) {
                $event = clone $event;
                $event->setStart(clone $eventDate);
                $this->eventMemo[$eventDate->format('Y-m-d')][] = $event;
            }

            switch ($event->getRepeat()) {
                case 'daily':
                    $eventDate->add(new DateInterval('P1D'));
                    break;
                case 'weekly':
                    $eventDate->add(new DateInterval('P1W'));
                    break;
                case 'monthly':
                    $eventDate->add(new DateInterval('P1M'));
                    break;
                case 'yearly':
                    $eventDate->add(new DateInterval('P1Y'));
                    break;
                default:
                    break 2;
            }
        }
    }
}
