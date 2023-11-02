<?php

declare(strict_types=1);

namespace Forumify\Forum\DataFixture;

use DateInterval;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Forumify\Core\DataFixture\UserFixture;
use Forumify\Core\Entity\User;
use Forumify\Forum\Entity\Message;
use Forumify\Forum\Entity\MessageThread;

class MessageFixture extends Fixture implements DependentFixtureInterface
{
    private DateTime $date;

    public function __construct() {
        $this->date = new DateTime('2000-01-01 00:00:00');
    }

    public function load(ObjectManager $manager)
    {
        $admin = $this->getReference(UserFixture::ADMIN_REFERENCE);
        $user = $this->getReference(UserFixture::USER_REFERENCE);

        $manager->persist($this->createThread('Hello there!', [$admin, $user], [
            $this->createMessage('Welcome to our forum!', $admin),
            $this->createMessage('Thanks admin :)', $user),
        ]));

        $manager->persist($this->createThread('Please help admin, I do not know how to create a message, this title is definitely too long!', [$user, $admin], [
            $this->createMessage('Hello admin, I was wondering how I could create a message?', $user),
            $this->createMessage('Oh that\'s simple! Just press the pencil icon next to "inbox".', $admin),
            $this->createMessage('Duhh, how did I miss that?!!!.', $user),
        ]));

        $manager->flush();
    }

    private function createMessage(string $content, User $user): Message
    {
        $message = new Message();
        $message->setContent($content);
        $message->setCreatedBy($user);
        $message->setCreatedAt($this->getCreatedAt());

        return $message;
    }

    private function createThread(string $title, array $participants, array $messages): MessageThread
    {
        $thread = new MessageThread();
        $thread->setTitle($title);

        foreach ($participants as $participant) {
            $thread->getParticipants()->add($participant);
        }

        $thread->setCreatedBy($participants[0]);
        $thread->setCreatedAt($this->getCreatedAt());

        foreach ($messages as $message) {
            $message->setThread($thread);
            $thread->getMessages()->add($message);
        }

        return $thread;
    }

    public function getDependencies(): array
    {
        return [UserFixture::class];
    }

    private function getCreatedAt(): DateTime
    {
        $this->date->add(new DateInterval('PT1H'));
        return clone $this->date;
    }
}
