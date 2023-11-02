<?php

declare(strict_types=1);

namespace Forumify\Forum\Controller;

use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\MessageThread;
use Forumify\Forum\Form\MessageReplyType;
use Forumify\Forum\Form\NewMessageThreadType;
use Forumify\Forum\Service\MessageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/messenger', 'messenger')]
class MessengerController extends AbstractController
{
    #[Route('/', '')]
    public function __invoke(): Response
    {
        return $this->render('@Forumify/frontend/messenger.html.twig');
    }

    #[Route('/create', '_thread_create')]
    public function createThread(Request $request, MessageService $messageService): Response
    {
        $this->denyAccessUnlessGranted(VoterAttribute::MessageThreadCreate->value);

        $form = $this->createForm(NewMessageThreadType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $thread = $messageService->createThread($form->getData());
            return $this->redirectToRoute('forumify_forum_messenger_thread', ['id' => $thread->getId()]);
        }

        return $this->render('@Forumify/frontend/forum/message_thread_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', '_thread')]
    public function thread(int $id): Response
    {
        return $this->render('@Forumify/frontend/messenger.html.twig', [
            'initialThreadId' => $id,
        ]);
    }

    #[Route('/{id}/reply', '_reply')]
    public function reply(Request $request, MessageThread $thread, MessageService $messageService): Response
    {
        $this->denyAccessUnlessGranted(VoterAttribute::MessageThreadView->value, $thread);

        $form = $this->createForm(MessageReplyType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $messageService->replyToThread($thread, $form->getData());
        }

        return $this->redirectToRoute('forumify_forum_messenger_thread', ['id' => $thread->getId()]);
    }
}
