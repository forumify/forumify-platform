<?php

declare(strict_types=1);

namespace Forumify\Calendar\Controller;

use Forumify\Calendar\Entity\Calendar;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CalendarController extends AbstractController
{
    #[Route('{slug?}', name: 'all')]
    public function __invoke(?Calendar $calendar): Response
    {
        return $this->render('@Forumify/frontend/calendar/calendar.html.twig', [
            'calendar' => $calendar,
        ]);
    }
}
