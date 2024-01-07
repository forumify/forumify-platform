<?php

declare(strict_types=1);

namespace Forumify\Core\Controller;

use Forumify\Core\Notification\NotificationMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class NotificationResendController extends AbstractController
{
    #[Route('notification/{id}/resend', 'notification_resend')]
    public function __invoke(int $id, MessageBusInterface $messageBus): Response
    {
        $messageBus->dispatch(new NotificationMessage($id));
        return new Response('ok');
    }
}
