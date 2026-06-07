<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Api\Forum;

use ApiPlatform\Metadata\IriConverterInterface;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\ForumGroup;
use Tests\Tests\Application\Api\ApiTestCase;
use Tests\Tests\Application\Api\Crud\DeleteTestTrait;
use Tests\Tests\Application\Api\Crud\GetCollectionTestTrait;
use Tests\Tests\Application\Api\Crud\GetTestTrait;
use Tests\Tests\Application\Api\Crud\PatchTestTrait;
use Tests\Tests\Application\Api\Crud\PostTestTrait;
use Tests\Tests\Factories\Forum\ForumFactory;
use Tests\Tests\Factories\Forum\ForumGroupFactory;

class ForumApiTest extends ApiTestCase
{
    use GetCollectionTestTrait;
    use GetTestTrait;
    use PostTestTrait;
    use PatchTestTrait;
    use DeleteTestTrait;

    protected static function factory(): string
    {
        return ForumFactory::class;
    }

    protected static function endpoint(): string
    {
        return '/api/forums';
    }

    protected function getPostBody(): array
    {
        return [
            'title' => 'Test Forum',
            'content' => 'This is a test forum',
            'type' => Forum::TYPE_MIXED,
        ];
    }

    protected function getPatchBody(): array
    {
        return ['title' => 'Updated Test Forum'];
    }

    public function testCreateSubforum(): void
    {
        $iri = self::getContainer()->get(IriConverterInterface::class);

        $parent = ForumFactory::createOne();
        $parentIri = $iri->getIriFromResource($parent);

        $group = ForumGroupFactory::createOne(['parentForum' => $parent]);
        $groupIri = $iri->getIriFromResource($group);

        $response = $this->post($this->endpoint(), [
            'json' => [
                'title' => 'Test Forum',
                'parent' => $parentIri,
                'group' => $groupIri,
            ],
        ]);

        self::assertArrayHasKey('parent', $response);
        self::assertSame($parentIri, $response['parent']);

        self::assertArrayHasKey('group', $response);
        self::assertSame($groupIri, $response['group']);
    }
}
