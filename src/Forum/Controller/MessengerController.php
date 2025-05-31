<?php

declare(strict_types=1);

namespace Forumify\Forum\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Forumify\Core\Entity\Notification;
use Forumify\Core\Entity\User;
use Forumify\Core\Notification\NotificationService;
use Forumify\Core\Repository\UserRepository;
use Forumify\Core\Security\VoterAttribute;
use Forumify\Core\Service\HTMLSanitizer;
use Forumify\Forum\Entity\Message;
use Forumify\Forum\Entity\MessageThread;
use Forumify\Forum\Form\MessageReplyType;
use Forumify\Forum\Form\NewMessageThread;
use Forumify\Forum\Form\NewMessageThreadType;
use Forumify\Forum\Notification\MessageUserAddedNotificationType;
use Forumify\Forum\Repository\MessageRepository;
use Forumify\Forum\Repository\MessageThreadRepository;
use Forumify\Forum\Service\MessageService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/messenger', 'messenger')]
class MessengerController extends AbstractController
{
    public function __construct(
        private readonly MessageThreadRepository $messageThreadRepository,
        private readonly MessageService $messageService,
        private readonly UserRepository $userRepository,
    ) {
    }

    #[Route('/', '')]
    public function __invoke(): Response
    {
        return $this->render('@Forumify/frontend/messenger/messenger.html.twig');
    }

    #[Route('/create', '_thread_create')]
    public function createThread(Request $request): Response
    {
        $this->denyAccessUnlessGranted(VoterAttribute::MessageThreadCreate->value);

        $data = new NewMessageThread();
        if ($request->get('recipient')) {
            $recipient = $this->userRepository->find($request->get('recipient'));
            if ($recipient !== null) {
                $data->setParticipants(new ArrayCollection([$recipient]));
            }
        }

        $form = $this->createForm(NewMessageThreadType::class, $data);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $thread = $this->messageService->createThread($form->getData());
            return $this->redirectToRoute('forumify_forum_messenger_thread', ['id' => $thread->getId()]);
        }

        return $this->render('@Forumify/frontend/forum/message_thread_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', '_thread')]
    public function thread(int $id): Response
    {
        return $this->render('@Forumify/frontend/messenger/messenger.html.twig', [
            'initialThreadId' => $id,
        ]);
    }

    #[Route('/{id}/reply', '_reply')]
    public function reply(Request $request, MessageThread $thread): Response
    {
        $this->denyAccessUnlessGranted(VoterAttribute::MessageThreadReply->value, $thread);

        $form = $this->createForm(MessageReplyType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->messageService->replyToThread($thread, $form->getData());
        }

        return $this->redirectToRoute('forumify_forum_messenger_thread', ['id' => $thread->getId()]);
    }

    #[Route('/{id}/add-participant', '_add_participant')]
    public function addParticipant(
        Request $request,
        MessageThread $thread,
        NotificationService $notificationService
    ): Response {
        $form = $this->createFormBuilder()
            ->add('participants', EntityType::class, [
                'multiple' => true,
                'autocomplete' => true,
                'class' => User::class,
                'choice_label' => fn (User $user) => $user->getDisplayName(),
                'query_builder' => fn (EntityRepository $repository) => $repository
                    ->createQueryBuilder('u')
                    ->where('u.id NOT IN (:participants)')
                    ->setParameter('participants', $thread->getParticipants()),
            ])
            ->getForm();

        $form->handleRequest($request);
        if (!($form->isSubmitted() && $form->isValid())) {
            return $this->render('@Forumify/form/simple_form_page.html.twig', [
                'title' => 'messenger.add_participant',
                'form' => $form->createView(),
                'cancelPath' => $this->generateUrl('forumify_forum_messenger_thread', ['id' => $thread->getId()]),
            ]);
        }

        $newParticipants = $form->get('participants')->getData();
        $participants = $thread->getParticipants();
        foreach ($newParticipants as $newParticipant) {
            $participants->add($newParticipant);
        }
        $this->messageThreadRepository->save($thread);

        foreach ($newParticipants as $newParticipant) {
            $notificationService->sendNotification(new Notification(
                MessageUserAddedNotificationType::TYPE,
                $newParticipant,
                [
                    'user' => $this->getUser(),
                    'messageThread' => $thread,
                ]
            ));
        }

        $this->addFlash('success', 'flashes.messenger_participants_added');
        return $this->redirectToRoute('forumify_forum_messenger_thread', ['id' => $thread->getId()]);
    }

    #[Route('/{id}/remove-participant/{participantId}', '_remove_participant')]
    public function removeParticipant(MessageThread $thread, int $participantId): Response
    {
        $participants = $thread->getParticipants();
        /** @var User $participant */
        foreach ($participants as $key => $participant) {
            if ($participant->getId() === $participantId) {
                $participants->remove($key);
                break;
            }
        }
        $this->messageThreadRepository->save($thread);

        $this->addFlash('success', 'flashes.messenger_participants_removed');
        return $this->redirectToRoute('forumify_forum_messenger_thread', ['id' => $thread->getId()]);
    }

    #[Route('/message/{id}/edit', '_message_edit', methods: ['POST'])]
    public function editMessage(
        Message $message,
        Request $request,
        MessageRepository $messageRepository,
        HTMLSanitizer $sanitizer,
    ): Response {
        if ($this->getUser()?->getUserIdentifier() !== $message->getCreatedBy()?->getUserIdentifier()) {
            throw $this->createAccessDeniedException();
        }

        $message->setContent($request->getContent());
        $messageRepository->save($message);

        return new Response($sanitizer->sanitize($message->getContent()));
    }
}
