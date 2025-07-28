<?php

declare(strict_types=1);

namespace Forumify\Forum\Serializer;

use ApiPlatform\Metadata\IriConverterInterface;
use ArrayObject;
use Forumify\Forum\Entity\Forum;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ForumApiSerializer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.jsonld.normalizer.item')]
        private readonly NormalizerInterface $apiNormalizer,
        private readonly IriConverterInterface $iriConverter,
    ) {
    }

    public function normalize(
        mixed $data,
        ?string $format = null,
        array $context = [],
    ): array|string|int|float|bool|ArrayObject|null {
        $result = $this->apiNormalizer->normalize($data, $format, $context);
        if (!is_array($result) || !$data instanceof Forum) {
            return $result;
        }

        return $result;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Forum;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Forum::class => true];
    }
}
