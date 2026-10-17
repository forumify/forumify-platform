<?php

declare(strict_types=1);

namespace Tests\Tests\Unit\Api\Serializer;

use ApiPlatform\Metadata\IriConverterInterface;
use Forumify\Core\Entity\SortableEntityInterface;
use Forumify\Core\Entity\SortableEntityTrait;
use Forumify\Api\Serializer\TraitSerializer;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class TraitSerializerTest extends TestCase
{
    public function testAddsPositionOfSortableEntities(): void
    {
        $data = new class implements SortableEntityInterface {
            use SortableEntityTrait;
        };
        $data->setPosition(3);

        $result = $this->createSerializer([])->normalize($data);

        self::assertSame(3, $result['position']);
    }

    public function testDoesNotOverwritePositionRelationOfNonSortableEntities(): void
    {
        $data = new class {
            public stdClass $position;

            public function __construct()
            {
                $this->position = new stdClass();
            }
        };
        $embedded = ['@id' => '/api/positions/1', 'name' => 'Squad Leader'];

        $result = $this->createSerializer(['position' => $embedded])->normalize($data);

        self::assertSame($embedded, $result['position']);
    }

    /**
     * @param array<string, mixed> $normalized
     */
    private function createSerializer(array $normalized): TraitSerializer
    {
        $decorated = $this->createMock(ObjectNormalizer::class);
        $decorated->method('normalize')->willReturn($normalized);

        $iriConverter = $this->createMock(IriConverterInterface::class);
        $iriConverter->method('getIriFromResource')->willReturn('/api/positions/1');

        return new TraitSerializer($decorated, $iriConverter);
    }
}
