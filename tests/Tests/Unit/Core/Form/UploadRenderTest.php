<?php

declare(strict_types=1);

namespace Tests\Tests\Unit\Core\Form;

use Forumify\Core\Form\UploadType;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Twig\Environment;

class UploadRenderTest extends KernelTestCase
{
    private const string IMAGE = 'holiday-photo-6a8746ee40c52.png';
    private const string DOCUMENT = 'quarterly-report-6a8746ee40c53.pdf';

    protected function setUp(): void
    {
        parent::setUp();

        $storage = self::getContainer()->get('media.storage');
        \assert($storage instanceof FilesystemOperator);
        $storage->write(self::IMAGE, (string) file_get_contents(TEST_DATA_DIR . '/forumify.png'));
        $storage->write(self::DOCUMENT, str_repeat('x', 2400000));
    }

    public function testEmptyStateOnlyRendersTheDropzone(): void
    {
        $html = $this->render(null);

        self::assertStringContainsString('class="upload-zone"', $html);
        self::assertStringNotContainsString('class="upload-file"', $html);
    }

    public function testExistingImageRendersAThumbnailNameAndSize(): void
    {
        $html = $this->render(self::IMAGE);

        self::assertStringContainsString('src="/storage/media/' . self::IMAGE . '"', $html);
        self::assertStringContainsString('>holiday-photo.png<', $html);
        self::assertStringContainsString('<span class="upload-sub">31 KB</span>', $html);
        self::assertStringContainsString('forumify--upload#removeExisting', $html);
    }

    public function testNonImageRendersAnExtensionChipInsteadOfAThumbnail(): void
    {
        $html = $this->render(self::DOCUMENT);

        self::assertStringContainsString('<span class="upload-ext">PDF</span>', $html);
        self::assertStringContainsString('<span class="upload-sub">2.3 MB</span>', $html);
        self::assertStringNotContainsString('<img src="/storage/media/' . self::DOCUMENT, $html);
    }

    public function testMultipleRendersEveryFileAndAcceptsSeveralAtOnce(): void
    {
        $html = $this->render([self::IMAGE, self::DOCUMENT], true);

        self::assertStringContainsString('multiple="multiple"', $html);
        self::assertStringContainsString('name="gallery[file][]"', $html);
        self::assertStringContainsString('class="upload-zone-hint"', $html);
        self::assertSame(2, substr_count($html, 'class="upload-file"'));
    }

    public function testRequiredSingleFileCannotBeRemoved(): void
    {
        $html = $this->render(self::IMAGE, false, true);

        self::assertStringNotContainsString('forumify--upload#removeExisting', $html);
    }

    private function render(mixed $data, bool $multiple = false, bool $required = false): string
    {
        $factory = self::getContainer()->get('form.factory');
        \assert($factory instanceof FormFactoryInterface);

        $form = $factory->createNamed('gallery', UploadType::class, $data, [
            'multiple' => $multiple,
            'required' => $required,
            'csrf_protection' => false,
            'filesystem' => 'media.storage',
            'asset_package' => 'forumify.media',
        ]);

        $twig = self::getContainer()->get('twig');
        \assert($twig instanceof Environment);

        return $twig
            ->createTemplate('{% form_theme form "@Forumify/form/theme.html.twig" %}{{ form_row(form) }}')
            ->render(['form' => $form->createView()]);
    }
}
