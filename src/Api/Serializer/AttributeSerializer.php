<?php

declare(strict_types=1);

namespace Forumify\Api\Serializer;

use ArrayObject;
use ReflectionClass;
use ReflectionObject;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @template T of object
 */
abstract class AttributeSerializer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    private NormalizerInterface&DenormalizerInterface&SerializerAwareInterface $decorated;

    #[Required]
    public function setServices(
        #[AutowireDecorated]
        NormalizerInterface&DenormalizerInterface&SerializerAwareInterface $decorated,
    ): void {
        $this->decorated = $decorated;
    }

    public function setSerializer(SerializerInterface $serializer): void
    {
        $this->decorated->setSerializer($serializer);
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (!class_exists($type) || !is_array($data)) {
            return $this->decorated->denormalize($data, $type, $format, $context);
        }

        $attr = $this->getAttributeClass();
        $refl = new ReflectionClass($type);
        foreach ($refl->getProperties() as $property) {
            $propertyName = $property->getName();
            $attributes = $property->getAttributes($attr);
            foreach ($attributes as $attribute) {
                $this->denormalizeProperty($data, $propertyName, $attribute->newInstance());
            }
        }

        return $this->decorated->denormalize($data, $type, $format, $context);
    }

    public function supportsDenormalization(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = [],
    ): bool {
        return $this->decorated->supportsDenormalization($data, $type, $format, $context);
    }

    public function normalize(
        mixed $data,
        ?string $format = null,
        array $context = [],
    ): array|string|int|float|bool|ArrayObject|null {
        $result = $this->decorated->normalize($data, $format, $context);
        if (!is_object($data) || !is_array($result)) {
            return $result;
        }

        $attr = $this->getAttributeClass();
        $refl = new ReflectionObject($data);
        foreach ($refl->getProperties() as $property) {
            $propertyName = $property->getName();
            $attributes = $property->getAttributes($attr);
            foreach ($attributes as $attribute) {
                $this->normalizeProperty($result, $data, $propertyName, $attribute->newInstance());
            }
        }

        return $result;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $this->decorated->supportsNormalization($data, $format, $context);
    }

    public function getSupportedTypes(?string $format): array
    {
        return $this->decorated->getSupportedTypes($format);
    }

    /**
     * @return class-string<T>
     */
    abstract protected function getAttributeClass(): string;

    /**
     * @param array<string, mixed> $result
     * @param T $attribute
     */
    protected function normalizeProperty(array &$result, object $data, string $property, object $attribute): void
    {
    }

    /**
     * @param array<string, mixed> $data
     * @param T $attribute
     */
    protected function denormalizeProperty(array &$data, string $property, object $attribute): void
    {
    }
}
