<?php

declare(strict_types=1);

namespace Forumify\Api\Serializer;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\State\SerializerContextBuilderInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\HttpFoundation\Request;

use function Symfony\Component\String\u;

#[AsDecorator('api_platform.serializer.context_builder')]
class SerializationContextBuilder implements SerializerContextBuilderInterface
{
    public function __construct(
        #[AutowireDecorated]
        private readonly SerializerContextBuilderInterface $decorated,
    ) {
    }

    /**
     * @param null|array<mixed> $extractedAttributes
     */
    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        $operation = $context['operation'] ?? null;
        if (!$operation instanceof Operation) {
            return $context;
        }

        $disabled = $operation->getExtraProperties()['disableContexts'] ?? false;
        if ($disabled) {
            return $context;
        }

        $groups = $context['groups'] ?? [];
        if (is_string($groups)) {
            $groups = [$groups];
        }

        $name = u($operation->getShortName())->camel()->title()->toString();
        $groups[] = $name;

        $additionalGroups = match (get_class($operation)) {
            Get::class => [$name . '::read', $name . '::get'],
            GetCollection::class => [$name . '::read', $name . '::collection'],
            Patch::class, Post::class => [$name . '::write'],
            default => [],
        };

        $context['groups'] = array_unique(array_merge($groups, $additionalGroups));
        return $context;
    }
}
