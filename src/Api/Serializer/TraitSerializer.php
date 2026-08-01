<?php

declare(strict_types=1);

namespace Forumify\Api\Serializer;

use ApiPlatform\Metadata\IriConverterInterface;
use ArrayObject;
use DateTimeInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\PropertyAccess\PropertyAccessorBuilder;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[AsDecorator('api_platform.jsonld.normalizer.item')]
class TraitSerializer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    private readonly PropertyAccessorInterface $propertyAccessor;

    public function __construct(
        #[AutowireDecorated]
        private readonly NormalizerInterface&DenormalizerInterface&SerializerAwareInterface $decorated,
        private readonly IriConverterInterface $iriConverter,
    ) {
        $this->propertyAccessor = new PropertyAccessorBuilder()
            ->disableExceptionOnInvalidPropertyPath()
            ->getPropertyAccessor();
    }

    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|ArrayObject|null
    {
        $result = $this->decorated->normalize($data, $format, $context);
        if (!is_array($result) || !is_object($data)) {
            return $result;
        }

        // IdentifiableEntityTrait
        $this->setIfExists($data, $result, 'id');

        // BlameableEntityTrait
        $this->setIfExists($data, $result, 'createdBy');
        $this->setIfExists($data, $result, 'updatedBy');

        // TimestampableEntityTrait
        $this->setIfExists($data, $result, 'createdAt');
        $this->setIfExists($data, $result, 'updatedAt');

        // SortableEntityTrait
        $this->setIfExists($data, $result, 'position');

        // HierarchicalEntityInterface
        $this->setIfExists($data, $result, 'parent');

        return $result;
    }

    /**
     * @param array<string, mixed> $result
     */
    private function setIfExists(object $data, array &$result, string $property): void
    {
        $value = $this->propertyAccessor->getValue($data, $property);
        if ($value === null) {
            return;
        }

        if ($value instanceof DateTimeInterface) {
            $value = $value->format('c');
        } elseif (is_object($value)) {
            $value = $this->iriConverter->getIriFromResource($value);
        }

        $result[$property] = $value;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $this->decorated->supportsNormalization($data, $format, $context);
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        return $this->decorated->denormalize($data, $type, $format, $context);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $this->decorated->supportsDenormalization($data, $type, $format, $context);
    }

    public function getSupportedTypes(?string $format): array
    {
        return $this->decorated->getSupportedTypes($format);
    }

    public function setSerializer(SerializerInterface $serializer): void
    {
        $this->decorated->setSerializer($serializer);
    }
}
