<?php

declare(strict_types=1);

namespace Tests\Tests\Unit\Core\Scheduler;

use DateInterval;
use DateTime;
use Forumify\Core\Repository\NotificationRepository;
use Forumify\Core\Scheduler\RemoveSeenNotificationsTask;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Tests\Factories\Core\NotificationFactory;
use Tests\Tests\Factories\Core\UserFactory;
use Zenstruck\Foundry\Test\Factories;

class RemoveSeenNotificationsTaskTest extends KernelTestCase
{
    use Factories;

    #[DataProvider('userWithNotificationsDataProvider')]
    public function testRemoveOldNotifications(callable $createUser, int $expectedCount): void
    {
        $user = $createUser();
        (self::getContainer()->get(RemoveSeenNotificationsTask::class))();

        $count = self::getContainer()->get(NotificationRepository::class)->count(['recipient' => $user->_real()]);
        self::assertEquals($expectedCount, $count);
    }

    public static function userWithNotificationsDataProvider(): iterable
    {
        $userWithSeen = function () {
            $user = UserFactory::createOne();
            NotificationFactory::createMany(20, [
                'seen' => true,
                'recipient' => $user,
            ]);
            return $user;
        };
        yield 'User with only seen' => [$userWithSeen, 10];

        $userWithUnseen = function () {
            $user = UserFactory::createOne();
            NotificationFactory::createMany(20, [
                'seen' => false,
                'recipient' => $user,
            ]);
            return $user;
        };
        yield 'User with only unseen' => [$userWithUnseen, 20];

        $userWithMixed = function () {
            $user = UserFactory::createOne();
            NotificationFactory::createMany(10, [
                'seen' => false,
                'createdAt' => (new DateTime())->sub(new DateInterval('P1D')),
                'recipient' => $user,
            ]);
            NotificationFactory::createMany(10, [
                'seen' => true,
                'recipient' => $user,
            ]);
            return $user;
        };
        yield 'User with a mix of seen/unseen' => [$userWithMixed, 10];

        $userWithReallyOld = function () {
            $user = UserFactory::createOne();
            NotificationFactory::createMany(20, [
                'seen' => false,
                'recipient' => $user,
                'createdAt' => new DateTime()->sub(new DateInterval('P1Y3M')),
            ]);
            return $user;
        };
        yield 'User with really old ones' => [$userWithReallyOld, 0];
    }
}
