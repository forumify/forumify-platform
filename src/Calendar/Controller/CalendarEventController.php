<?php

declare(strict_types=1);

namespace Forumify\Calendar\Controller;

use Forumify\Calendar\Entity\CalendarEvent;
use Forumify\Calendar\Form\EventType;
use Forumify\Calendar\Repository\CalendarEventRepository;
use Forumify\Calendar\Repository\CalendarRepository;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Core\Service\MediaService;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/event', 'event')]
class CalendarEventController extends AbstractController
{
    public function __construct(
        private readonly CalendarRepository $calendarRepository,
        private readonly CalendarEventRepository $calendarEventRepository,
        private readonly FilesystemOperator $assetStorage,
        private readonly MediaService $mediaService,
    ) {
    }

    #[Route('/create', '_create')]
    public function create(Request $request): Response
    {
        $event = new CalendarEvent();

        $calendarId = $request->get('calendar');
        if ($calendarId !== null) {
            $calendar = $this->calendarRepository->find($calendarId);
            if ($calendar !== null) {
                $event->setCalendar($calendar);
            }
        }

        return $this->handleForm($event, $request, true);
    }

    #[Route('/{slug:event}/edit', '_edit')]
    public function edit(CalendarEvent $event, Request $request): Response
    {
        $this->denyAccessUnlessGranted(VoterAttribute::ACL->value, [
            'entity' => $event->getCalendar(),
            'permission' => 'manage_events',
        ]);

        return $this->handleForm($event, $request, false);
    }

    #[Route('/{slug:event}/delete', '_delete')]
    public function delete(CalendarEvent $event, Request $request): Response
    {
        $this->denyAccessUnlessGranted(VoterAttribute::ACL->value, [
            'entity' => $event->getCalendar(),
            'permission' => 'manage_events',
        ]);

        if (!$request->get('confirmed')) {
            return $this->render('@Forumify/frontend/calendar/delete_event.html.twig', [
                'event' => $event,
            ]);
        }

        $this->calendarEventRepository->remove($event);

        $this->addFlash('success', 'calendar.event.deleted');
        return $this->redirectToRoute('forumify_calendar_all');
    }

    private function handleForm(CalendarEvent $event, Request $request, bool $isNew): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('@Forumify/form/simple_form_page.html.twig', [
                'form' => $form->createView(),
                'title' => $isNew ? 'calendar.event.create' : 'calendar.event.edit',
                'cancelPath' =>$this->generateUrl('forumify_calendar_all'),
            ]);
        }

        /** @var CalendarEvent $event */
        $event = $form->getData();
        $this->denyAccessUnlessGranted(VoterAttribute::ACL->value, [
            'entity' => $event->getCalendar(),
            'permission' => 'manage_events',
        ]);

        $newBanner = $form->get('newBanner')->getData();
        if ($newBanner instanceof UploadedFile) {
            $banner = $this->mediaService->saveToFilesystem($this->assetStorage, $newBanner);
            $event->setBanner($banner);
        }

        $this->calendarEventRepository->save($event);

        $this->addFlash('success', 'calendar.event.created');
        return $this->redirectToRoute('forumify_calendar_all');
    }
}
