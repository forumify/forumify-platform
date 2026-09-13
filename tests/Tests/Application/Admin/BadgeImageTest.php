<?php

declare(strict_types=1);

namespace Application\Admin;

use Forumify\Admin\Crud\Event\PreSaveCrudEvent;
use Forumify\Forum\Entity\Badge;
use Forumify\Forum\Repository\BadgeRepository;
use Forumify\Testing\Traits\UserTrait;
use League\Flysystem\FilesystemOperator;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BadgeImageTest extends WebTestCase
{
    use UserTrait;

    private const string EXISTING = 'existing-badge.png';

    public function testReplacingTheImageDeletesTheFileItReplaced(): void
    {
        $client = $this->openEditPage($badge);

        $client->submitForm('Save', [
            'badge[name]' => 'Renamed Badge',
            'badge[image][file]' => $this->image(),
        ]);

        self::assertResponseRedirects();

        $badge = $this->refresh($badge);
        self::assertSame('Renamed Badge', $badge->getName());
        self::assertNotSame(self::EXISTING, $badge->getImage());
        self::assertTrue($this->storage()->has($badge->getImage()));
        self::assertFalse($this->storage()->has(self::EXISTING));
    }

    public function testAFailedSaveKeepsTheOldImageAndRollsBackTheNewOne(): void
    {
        $client = $this->openEditPage($badge);
        $before = $this->storedFiles();

        $client->disableReboot();
        $dispatcher = self::getContainer()->get('event_dispatcher');
        self::assertInstanceOf(EventDispatcherInterface::class, $dispatcher);
        $dispatcher->addListener(
            PreSaveCrudEvent::getName(Badge::class),
            static fn (): never => throw new RuntimeException('save failed'),
        );

        $client->catchExceptions(true);
        $client->submitForm('Save', [
            'badge[name]' => 'Renamed Badge',
            'badge[image][file]' => $this->image(),
        ]);

        self::assertResponseStatusCodeSame(500);
        self::assertSame($before, $this->storedFiles());

        $badge = $this->refresh($badge);
        self::assertSame('Old Badge', $badge->getName());
        self::assertSame(self::EXISTING, $badge->getImage());
    }

    private function openEditPage(?Badge &$badge): KernelBrowser
    {
        $client = static::createClient();

        $this->storage()->write(self::EXISTING, (string) file_get_contents(TEST_DATA_DIR . '/forumify.png'));

        $badge = new Badge();
        $badge->setName('Old Badge');
        $badge->setDescription('');
        $badge->setImage(self::EXISTING);
        self::getContainer()->get(BadgeRepository::class)->save($badge);

        $client->loginUser($this->createAdmin());
        $client->request('GET', "/admin/badges/{$badge->getId()}/edit");
        self::assertResponseIsSuccessful();

        return $client;
    }

    private function refresh(Badge $badge): Badge
    {
        $repository = self::getContainer()->get(BadgeRepository::class);
        $repository->getEntityManager()->clear();

        $refreshed = $repository->find($badge->getId());
        self::assertInstanceOf(Badge::class, $refreshed);

        return $refreshed;
    }

    private function image(): UploadedFile
    {
        return new UploadedFile(TEST_DATA_DIR . '/forumify.png', 'replacement.png', 'image/png', null, true);
    }

    private function storage(): FilesystemOperator
    {
        $storage = self::getContainer()->get('asset.storage');
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
