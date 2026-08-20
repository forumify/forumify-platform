<?php

declare(strict_types=1);

namespace Tests\Tests\Unit\Core\Form;

use Forumify\Core\Form\UploadType;
use Forumify\Core\Service\FileDeletionQueue;
use Forumify\Core\Service\MediaService;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\NullLogger;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Translation\IdentityTranslator;

class UploadTypeTest extends TypeTestCase
{
    private const string STORAGE = 'test.storage';

    private string $storageDir;
    private FilesystemOperator $storage;
    private FileDeletionQueue $deletionQueue;

    protected function setUp(): void
    {
        $this->storageDir = sys_get_temp_dir() . '/forumify-upload-test-' . uniqid();
        mkdir($this->storageDir);

        $this->storage = new Filesystem(new LocalFilesystemAdapter($this->storageDir));
        $this->deletionQueue = new FileDeletionQueue(new NullLogger());

        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        array_map(unlink(...), glob($this->storageDir . '/*') ?: []);
        rmdir($this->storageDir);
    }

    protected function getExtensions(): array
    {
        $packages = new Packages(new Package(new EmptyVersionStrategy()), [
            'test.package' => new Package(new EmptyVersionStrategy()),
        ]);

        $type = new UploadType(
            $packages,
            new MediaService(new AsciiSlugger()),
            $this->deletionQueue,
            new IdentityTranslator(),
            new \ArrayIterator([self::STORAGE => $this->storage]),
        );

        return [new PreloadedExtension([$type], []), new HttpFoundationExtension()];
    }

    public function testKeepsExistingFileWhenNothingIsSubmitted(): void
    {
        $form = $this->createUploadForm('existing.png');
        $form->submit(['file' => null, 'removed' => '']);

        self::assertSame('existing.png', $form->getData());
        self::assertSame([], $this->storedFiles());
    }

    public function testUploadReplacesExistingFileAndQueuesItForDeletion(): void
    {
        $this->storage->write('existing.png', 'old');

        $form = $this->createUploadForm('existing.png');
        $form->submit(['file' => $this->upload('avatar.png'), 'removed' => '']);

        $stored = $this->storedFiles();
        self::assertCount(2, $stored);
        self::assertSame($form->getData(), array_values(array_diff($stored, ['existing.png']))[0]);
        self::assertStringStartsWith('avatar-', (string) $form->getData());

        $this->deletionQueue->commit();
        self::assertSame([$form->getData()], $this->storedFiles());
    }

    public function testRemovingClearsTheValueAndDeletesTheFile(): void
    {
        $this->storage->write('existing.png', 'old');

        $form = $this->createUploadForm('existing.png');
        $form->submit(['file' => null, 'removed' => 'existing.png']);

        self::assertNull($form->getData());

        $this->deletionQueue->commit();
        self::assertSame([], $this->storedFiles());
    }

    public function testDiscardedQueueLeavesFilesUntouched(): void
    {
        $this->storage->write('existing.png', 'old');

        $form = $this->createUploadForm('existing.png');
        $form->submit(['file' => null, 'removed' => 'existing.png']);

        $this->deletionQueue->discard();
        self::assertSame(['existing.png'], $this->storedFiles());
    }

    public function testRemovingAPathThatWasNotOnTheEntityIsIgnored(): void
    {
        $this->storage->write('existing.png', 'old');
        $this->storage->write('someone-elses.png', 'secret');

        $form = $this->createUploadForm('existing.png');
        $form->submit(['file' => null, 'removed' => 'someone-elses.png']);

        self::assertSame('existing.png', $form->getData());

        $this->deletionQueue->commit();
        self::assertSame(['existing.png', 'someone-elses.png'], $this->storedFiles());
    }

    public function testMultipleAppendsUploadsToTheExistingList(): void
    {
        $form = $this->createUploadForm(['one.png', 'two.png'], true);
        $form->submit([
            'file' => [$this->upload('three.png'), $this->upload('four.png')],
            'removed' => 'one.png',
        ]);

        $data = $form->getData();
        self::assertIsArray($data);
        self::assertCount(3, $data);
        self::assertSame('two.png', $data[0]);
        self::assertStringStartsWith('three-', $data[1]);
        self::assertStringStartsWith('four-', $data[2]);
    }

    #[DataProvider('unsafeFilenameProvider')]
    public function testStoredFilenamesNeverContainCommas(string $filename): void
    {
        $form = $this->createUploadForm([], true);
        $form->submit(['file' => [$this->upload($filename)], 'removed' => '']);

        $data = $form->getData();
        self::assertIsArray($data);
        self::assertStringNotContainsString(',', $data[0]);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function unsafeFilenameProvider(): iterable
    {
        yield 'comma' => ['a,b.png'];
        yield 'many commas' => ['a,,,b,c.png'];
        yield 'unicode' => ['naïve, résumé.png'];
    }

    /**
     * @return FormInterface<mixed>
     */
    private function createUploadForm(mixed $data, bool $multiple = false): FormInterface
    {
        return $this->factory->create(UploadType::class, $data, [
            'filesystem' => self::STORAGE,
            'asset_package' => 'test.package',
            'multiple' => $multiple,
        ]);
    }

    private function upload(string $filename): UploadedFile
    {
        $path = $this->storageDir . '/../' . uniqid('upload-');
        file_put_contents($path, 'contents');

        return new UploadedFile($path, $filename, 'image/png', null, true);
    }

    /**
     * @return list<string>
     */
    private function storedFiles(): array
    {
        $files = array_map(
            static fn (string $path): string => basename($path),
            glob($this->storageDir . '/*') ?: [],
        );
        sort($files);

        return $files;
    }
}
