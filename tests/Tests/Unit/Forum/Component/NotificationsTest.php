<?php

declare(strict_types=1);

namespace Tests\Tests\Unit\Forum\Component;

use Doctrine\ORM\EntityManagerInterface;
use Forumify\Core\Entity\Notification;
use Forumify\Core\Notification\NotificationTypeCollection;
use Forumify\Core\Repository\NotificationRepository;
use Forumify\Forum\Component\Notifications;
use Forumify\Forum\Notification\CommentCreatedNotificationType;
use Forumify\Testing\Factories\Core\UserFactory;
use Forumify\Testing\Factories\Forum\CommentFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use Zenstruck\Foundry\Test\Factories;

class NotificationsTest extends KernelTestCase
{
    use Factories;

    public function testNotificationForADeletedCommentIsPrunedWhenListedOnTheFrontend(): void
    {
        $user = UserFactory::createOne();
        $comment = CommentFactory::createOne();

        $notificationRepository = self::getContainer()->get(NotificationRepository::class);
        $notification = new Notification(CommentCreatedNotificationType::TYPE, $user, [
            'comment' => $comment,
        ]);
        $notificationRepository->save($notification);
        $notificationId = $notification->getId();

        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->remove($comment);
        $em->flush();

        $notifications = $this->createComponent($user)->getNotifications();

        self::assertCount(0, $notifications);
        self::assertNull($notificationRepository->find($notificationId));
    }

    public function testNotificationForAnExistingCommentIsKept(): void
    {
        $user = UserFactory::createOne();
        $comment = CommentFactory::createOne();

        $notificationRepository = self::getContainer()->get(NotificationRepository::class);
        $notification = new Notification(CommentCreatedNotificationType::TYPE, $user, [
            'comment' => $comment,
        ]);
        $notificationRepository->save($notification);
        $notificationId = $notification->getId();

        $notifications = $this->createComponent($user)->getNotifications();

        self::assertCount(1, $notifications);
        self::assertNotNull($notificationRepository->find($notificationId));
    }

    private function createComponent(UserInterface $user): Notifications
    {
        $container = self::getContainer();

        return new class(
            $container->get(NotificationRepository::class),
            $container->get(NotificationTypeCollection::class),
            $user,
        ) extends Notifications {
            public function __construct(
                NotificationRepository $notificationRepository,
                NotificationTypeCollection $notificationTypeCollection,
                private readonly UserInterface $testUser,
            ) {
                parent::__construct($notificationRepository, $notificationTypeCollection);
            }

            protected function getUser(): ?UserInterface
            {
                return $this->testUser;
            }
        };
    }
}
