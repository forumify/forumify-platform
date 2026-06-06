<?php

declare(strict_types=1);

namespace Forumify\Api\Serializer;

use ApiPlatform\Metadata\IriConverterInterface;
use ArrayObject;
use Forumify\Core\Entity\BlameableEntityTrait;
use Forumify\Core\Entity\IdentifiableEntityTrait;
use Forumify\Core\Entity\SortableEntityInterface;
use Forumify\Core\Entity\TimestampableEntityTrait;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[AsDecorator('api_platform.jsonld.normalizer.item')]
class TraitSerializer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    /** @var array<class-string, array<string, string>> */
    private array $uses = [];

    public function __construct(
        #[AutowireDecorated]
        private readonly NormalizerInterface&DenormalizerInterface&SerializerAwareInterface $decorated,
        private readonly IriConverterInterface $iriConverter,
    ) {
    }

    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|ArrayObject|null
    {
        $result = $this->decorated->normalize($data, $format, $context);
        if (!is_array($result) || !is_object($data)) {
            return $result;
        }

        $traits = $this->getUsedTraits($data);
        if (array_key_exists(IdentifiableEntityTrait::class, $traits)) {
            $result['id'] = $data->getId();
        }

        if (array_key_exists(BlameableEntityTrait::class, $traits)) {
            if ($user = $data->getCreatedBy()) {
                $result['createdBy'] = $this->iriConverter->getIriFromResource($user);
            }
            if ($user = $data->getUpdatedBy()) {
                $result['updatedBy'] = $this->iriConverter->getIriFromResource($user);
            }
        }

        if (array_key_exists(TimestampableEntityTrait::class, $traits)) {
            if ($date = $data->getCreatedAt()) {
                $result['createdAt'] = $date->format('c');
            }
            if ($date = $data->getUpdatedAt()) {
                $result['createdAt'] = $date->format('c');
            }
        }

        if (array_key_exists(SortableEntityInterface::class, $traits)) {
            $result['position'] = $data->getPosition();
        }

        return $result;
    }

    /**
     * @return array<string, string>
     */
    private function getUsedTraits(object $data): array
    {
        $cls = get_class($data);
        if (isset($this->uses[$cls])) {
            return $this->uses[$cls];
        }

        $uses = class_uses($data);
        $this->uses[$cls] = $uses;
        return $uses;
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
