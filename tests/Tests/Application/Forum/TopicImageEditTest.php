<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Forum;

use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\Topic;
use Forumify\Forum\Form\TopicData;
use Forumify\Forum\Repository\ForumRepository;
use Forumify\Forum\Repository\TopicRepository;
use Forumify\Forum\Service\CreateTopicService;
use Forumify\Testing\Traits\ACLTrait;
use Forumify\Testing\Traits\UserTrait;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class TopicImageEditTest extends WebTestCase
{
    use UserTrait;
    use ACLTrait;

    private const string EXISTING = 'existing-image.png';

    public function testReplacingTheImageDeletesTheFileItReplaced(): void
    {
        $client = $this->openEditPage($topic);

        $client->submitForm('Save', [
            'topic[title]' => 'Renamed Topic',
            'topic[image][file]' => $this->image(),
        ]);

        self::assertResponseIsSuccessful();

        $topic = $this->refresh($topic);
        self::assertSame('Renamed Topic', $topic->getTitle());
        self::assertNotSame(self::EXISTING, $topic->getImage());
        self::assertTrue($this->storage()->has((string) $topic->getImage()));
        self::assertFalse($this->storage()->has(self::EXISTING));
    }

    public function testUploadRejectedByValidationNeverReachesStorage(): void
    {
        $client = $this->openEditPage($topic);
        $before = $this->storedFiles();

        $client->submitForm('Save', [
            'topic[title]' => 'Renamed Topic',
            'topic[image][file]' => $this->notAnImage(),
        ]);

        self::assertResponseIsSuccessful();
        self::assertSame($before, $this->storedFiles());

        $topic = $this->refresh($topic);
        self::assertSame('Test Topic', $topic->getTitle());
        self::assertSame(self::EXISTING, $topic->getImage());
    }

    private function openEditPage(?Topic &$topic): KernelBrowser
    {
        $client = static::createClient();
        $client->followRedirects();

        $forum = new Forum();
        $forum->setTitle('Image Forum');
        $forum->setType(Forum::TYPE_IMAGE);
        self::getContainer()->get(ForumRepository::class)->save($forum);

        $this->createACL(Forum::class, $forum->getId(), 'view');
        $this->createACL(Forum::class, $forum->getId(), 'moderate');

        $this->storage()->write(self::EXISTING, (string) file_get_contents(TEST_DATA_DIR . '/forumify.png'));

        $topicData = new TopicData();
        $topicData->setTitle('Test Topic');
        $topicData->setContent('<p>body</p>');
        $topicData->setImage(self::EXISTING);
        $topic = self::getContainer()->get(CreateTopicService::class)->createTopic($forum, $topicData);

        $client->loginUser($this->createUser());
        $client->request('GET', "/topic/{$topic->getSlug()}/edit");
        self::assertResponseIsSuccessful();

        return $client;
    }

    private function refresh(Topic $topic): Topic
    {
        $repository = self::getContainer()->get(TopicRepository::class);
        $repository->getEntityManager()->clear();

        $refreshed = $repository->find($topic->getId());
        self::assertInstanceOf(Topic::class, $refreshed);

        return $refreshed;
    }

    private function image(): UploadedFile
    {
        return new UploadedFile(TEST_DATA_DIR . '/forumify.png', 'replacement.png', 'image/png', null, true);
    }

    private function notAnImage(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'forumify-not-an-image');
        self::assertIsString($path);
        file_put_contents($path, 'this is not an image');

        return new UploadedFile($path, 'sneaky.png', 'image/png', null, true);
    }

    private function storage(): FilesystemOperator
    {
        $storage = self::getContainer()->get('media.storage');
        self::assertInstanceOf(FilesystemOperator::class, $storage);

        return $storage;
    }

    /**
     * @return list<string>
     */
    private function storedFiles(): array
    {
        $files = $this->storage()
            ->listContents('')
            ->map(static fn (mixed $item): string => (string) $item->path())
            ->toArray();
        sort($files);

        return array_values($files);
    }
}
