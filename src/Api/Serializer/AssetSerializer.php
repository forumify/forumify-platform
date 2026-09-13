<?php

declare(strict_types=1);

namespace Forumify\Api\Serializer;

use Forumify\Api\Entity\NewAsset;
use Forumify\Api\Serializer\Attribute\Asset;
use League\Flysystem\FilesystemOperator;
use LogicException;
use RuntimeException;
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @extends AttributeSerializer<Asset>
 */
#[AsDecorator('api_platform.jsonld.normalizer.item')]
class AssetSerializer extends AttributeSerializer
{
    /** @var array<string, FilesystemOperator> */
    private array $storages = [];

    /**
     * @param iterable<FilesystemOperator> $storages
     */
    public function __construct(
        private readonly Packages $packages,
        private readonly PropertyAccessorInterface $propertyAccessor,
        private readonly SluggerInterface $slugger,
        #[AutowireIterator('flysystem.storage', 'storage')]
        iterable $storages,
    ) {
        $this->storages = iterator_to_array($storages);
    }

    protected function getAttributeClass(): string
    {
        return Asset::class;
    }

    protected function normalizeProperty(array &$result, object $data, string $property, object $attribute): void
    {
        $value = $this->propertyAccessor->getValue($data, $attribute->field);
        if (empty($value)) {
            return;
        }

        $result[$attribute->field] = is_array($value)
            ? array_map(fn (string $path): string => $this->packages->getUrl($path, $attribute->package), array_values($value))
            : $this->packages->getUrl($value, $attribute->package);
    }

    protected function denormalizeProperty(array &$data, string $property, object $attribute): void
    {
        $value = $data[$property] ?? null;
        if (!is_array($value)) {
            return;
        }
        unset($data[$property]);

        $storage = $this->storages[$attribute->storage]
            ?? throw new LogicException($attribute->storage . ' does not exist.');

        $data[$attribute->field] = array_is_list($value)
            ? array_map(fn (mixed $asset): string => $this->write($storage, $this->toAsset($asset, $property)), $value)
            : $this->write($storage, $this->toAsset($value, $property));
    }

    private function toAsset(mixed $value, string $property): NewAsset
    {
        if (!is_array($value) || !is_string($value['data'] ?? null)) {
            throw new UnexpectedValueException("\"$property\" expects assets with a base64 encoded \"data\" property.");
        }

        $asset = new NewAsset();
        $asset->data = $value['data'];
        $asset->filename = is_string($value['filename'] ?? null) ? $value['filename'] : null;

        return $asset;
    }

    private function write(FilesystemOperator $storage, NewAsset $asset): string
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'api-upload-');
        $handle = fopen($tmpFile, 'rwb+');
        if (!$handle) {
            throw new RuntimeException('Unable to get handle for writing.');
        }
        fwrite($handle, base64_decode($asset->data));
        rewind($handle);

        $destination = $this->createDestination($tmpFile, $asset->filename);
        $storage->writeStream($destination, $handle);

        fclose($handle);
        @unlink($tmpFile);

        return $destination;
    }

    private function createDestination(string $file, ?string $providedName): string
    {
        $pathinfo = $providedName ? pathinfo($providedName) : null;

        $dest = uniqid();
        if ($name = ($pathinfo['filename'] ?? null)) {
            $dest .= '-' . $this->slugger->slug($name);
        }
        $dest .= '.' . $this->getExtension($file, $pathinfo['extension'] ?? null);
        return $dest;
    }

    private function getExtension(string $path, ?string $providedExtension): string
    {
        $mime = new MimeTypes();
        $fileMime = $mime->guessMimeType($path);
        if ($fileMime === null && $providedExtension === null) {
            throw new RuntimeException('Unable to determine mimetype of file. Retry upload while providing a "filename".');
        }

        if ($fileMime !== null) {
            $ext = $mime->getExtensions($fileMime)[0] ?? null;
            if ($ext) {
                return $ext;
            }
        }

        if ($providedExtension !== null) {
            return $providedExtension;
        }

        throw new LogicException('This should be unreachable');
    }
}
