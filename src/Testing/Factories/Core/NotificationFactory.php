<?php

declare(strict_types=1);

namespace Forumify\Testing\Factories\Core;

use Forumify\Core\Entity\Notification;
use Forumify\Core\Notification\GenericNotificationType;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Notification>
 */
class NotificationFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Notification::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'recipient' => UserFactory::randomOrCreate(),
            'type' => GenericNotificationType::TYPE,
            'context' => [
                'title' => 'Test Notification',
                'description' => 'This is a test notification created by foundry.',
            ],
        ];
    }
}
