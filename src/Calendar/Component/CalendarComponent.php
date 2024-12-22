<?php

declare(strict_types=1);

namespace Forumify\Calendar\Component;

use DateInterval;
use DateTime;
use DateTimeZone;
use Forumify\Calendar\Entity\Calendar;
use Forumify\Calendar\Repository\CalendarRepository;
use Forumify\Calendar\Service\CalendarService;
use Forumify\Core\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('Forumify\\Calendar', '@Forumify/frontend/components/calendar/calendar.html.twig')]
class CalendarComponent
{
    use DefaultActionTrait;

    #[LiveProp]
    public DateTime $now;

    #[LiveProp]
    public DateTime $view;

    #[LiveProp]
    public ?Calendar $selectedCalendar = null;

    public function __construct(
        private readonly Security $security,
        public readonly CalendarRepository $calendarRepository,
        private readonly CalendarService $calendarService,
    ) {
    }

    public function mount(): void
    {
        $this->now = new DateTime();
        $this->setTimezone($this->now);

        $this->view = clone $this->now;
    }

    public function getStartDay(): DateTime
    {
        $firstDay = clone $this->view;
        $firstDay->modify('first day of this month');

        $offset = (int)$firstDay->format('N') - 1;
        $firstDay->sub(new DateInterval("P{$offset}D"));
        return $firstDay;
    }

    public function getEvents(DateTime $date): array
    {
        $events = $this->calendarService->getAllEvents($this->view, $this->selectedCalendar);
        return $events[$date->format('Y-m-d')] ?? [];
    }

    #[LiveAction]
    public function prevMonth(): void
    {
        $this->view->modify('first day of previous month');
        $this->setTimezone($this->view);
    }

    #[LiveAction]
    public function nextMonth(): void
    {
        $this->view->modify('first day of next month');
        $this->setTimezone($this->view);
    }

    private function setTimezone(DateTime $date): void
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return;
        }
        $date->setTimezone(new DateTimeZone($user->getTimezone()));
    }
}
