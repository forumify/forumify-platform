<?php

declare(strict_types=1);

namespace Forumify\Api\Serializer;

use Forumify\Api\Serializer\Attribute\Asset;
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

/**
 * @extends AttributeSerializer<Asset>
 */
#[AsDecorator('api_platform.jsonld.normalizer.item')]
class AssetSerializer extends AttributeSerializer
{
    public function __construct(
        private readonly Packages $packages,
    ) {
    }

    protected function getAttributeClass(): string
    {
        return Asset::class;
    }

    protected function normalizeProperty(mixed $value, object $attribute): mixed
    {
        if ($value === null) {
            return null;
        }

        return $this->packages->getUrl($value, $attribute->storage);
    }
}
