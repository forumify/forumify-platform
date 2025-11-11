<?php

declare(strict_types=1);

namespace Forumify\Api\Serializer;

use ArrayObject;
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
    ) {
        $this->decorated = $decorated;
    }

    public function setSerializer(SerializerInterface $serializer): void
    {
        $this->decorated->setSerializer($serializer);
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
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
        if (!is_object($data)) {
            return $result;
        }

        $attr = $this->getAttributeClass();
        $refl = new ReflectionObject($data);
        foreach ($refl->getProperties() as $property) {
            $attributes = $property->getAttributes($attr);
            foreach ($attributes as $attribute) {
                $attrInstance = $attribute->newInstance();
                $value = $property->getValue($data);

                $data = $this->normalizeProperty($value, $attrInstance);
                if ($data !== null) {
                    $result[$property->getName()] = $data;
                }
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
     * @param T $attribute
     */
    protected function normalizeProperty(mixed $value, object $attribute): mixed
    {
        return $value;
    }

    /**
     * @param T $attribute
     */
    protected function denormalizeProperty(mixed $value, object $attribute): mixed
    {
        return $value;
    }
}
