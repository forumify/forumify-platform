<?php

declare(strict_types=1);

namespace Forumify\Api\Serializer;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\SerializerContextBuilderInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\TypeInfo\Type\CollectionType;
use Symfony\Component\TypeInfo\Type\NullableType;

use function Symfony\Component\String\u;

#[AsDecorator('api_platform.serializer.context_builder')]
class ContextBuilder implements SerializerContextBuilderInterface
{
    private readonly PropertyInfoExtractor $propertyInfoExtractor;

    public function __construct(
        #[AutowireDecorated]
        private readonly SerializerContextBuilderInterface $decorated,
    ) {
        $this->propertyInfoExtractor = new PropertyInfoExtractor(typeExtractors: [
            new PhpDocExtractor(),
            new ReflectionExtractor(),
        ]);
    }

    /**
     * @param null|array<mixed> $extractedAttributes
     */
    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        if (empty($context['operation'])) {
            return $context;
        }

        /** @var Operation $operation */
        $operation = $context['operation'];
        $disabled = $operation->getExtraProperties()['disableContexts'] ?? false;
        if ($disabled) {
            return $context;
        }

        $groups = $context['groups'] ?? [];
        if (is_string($groups)) {
            $groups = [$groups];
        }

        $groups[] = u($operation->getShortName())->camel()->title()->toString();

        $this->addIncludes($groups, $operation, $request);
        $context['groups'] = array_unique($groups);

        return $context;
    }

    /**
     * @param array<string> $groups
     */
    private function addIncludes(array &$groups, Operation $operation, Request $request): void
    {
        $includes = $request->get('_include', '');
        $includes = array_filter(array_map('trim', explode(',', $includes)));
        if (empty($includes)) {
            return;
        }

        /** @var class-string|null $operationClass */
        $operationClass = $operation->getClass();
        if ($operationClass === null) {
            return;
        }

        foreach ($includes as $include) {
            $this->addInclude($groups, $operationClass, explode('.', $include));
        }
    }

    /**
     * @param array<string> $groups
     * @param class-string $class
     * @param array<string> $include
     */
    private function addInclude(array &$groups, string $class, array $include): void
    {
        if (empty($include)) {
            return;
        }

        $current = array_shift($include);
        $type = $this->propertyInfoExtractor->getType($class, $current);
        if ($type instanceof NullableType) {
            $type = $type->getWrappedType();
        }

        if ($type instanceof CollectionType) {
            $type = $type->getCollectionValueType();
        }

        /** @var class-string $typeName */
        $typeName = (string)$type;
        if (!class_exists($typeName)) {
            return;
        }

        $groups[] = substr($typeName, strrpos($typeName, '\\') + 1);
        $this->addInclude($groups, $typeName, $include);
    }
}
