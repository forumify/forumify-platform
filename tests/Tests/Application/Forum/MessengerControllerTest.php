<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Forum;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Tests\Factories\Core\UserFactory;
use Tests\Tests\Traits\UserTrait;
use Zenstruck\Foundry\Test\Factories;

class MessengerControllerTest extends WebTestCase
{
    use UserTrait;
    use Factories;

    public function testSendMessage(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        $client->loginUser($this->createAdmin());
        $recipient = UserFactory::createOne();

        $c = $client->request('GET', '/messenger');
        self::assertResponseIsSuccessful();

        $c = $c->filter('a[href="/messenger/create"]')->link();
        $client->click($c);
        self::assertResponseIsSuccessful();

        $client->submitForm('Send message', [
            'new_message_thread[title]' => 'test',
            'new_message_thread[participants]' => [$recipient->getId()],
            'new_message_thread[message]' => '<p>This is a test message</p>',
        ]);
        self::assertResponseIsSuccessful();
        self::assertAnySelectorTextContains('p', 'This is a test message');
    }
}
