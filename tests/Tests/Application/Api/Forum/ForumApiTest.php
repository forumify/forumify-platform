<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Api\Forum;

use ApiPlatform\Metadata\IriConverterInterface;
use Forumify\Forum\Entity\Forum;
use Symfony\Component\HttpClient\Exception\ClientException;
use Forumify\Testing\Api\ApiTestCase;
use Forumify\Testing\Api\Crud\DeleteTestTrait;
use Forumify\Testing\Api\Crud\GetCollectionTestTrait;
use Forumify\Testing\Api\Crud\GetTestTrait;
use Forumify\Testing\Api\Crud\PatchTestTrait;
use Forumify\Testing\Api\Crud\PostTestTrait;
use Forumify\Testing\Factories\Forum\ForumFactory;
use Forumify\Testing\Factories\Forum\ForumGroupFactory;
use Forumify\Testing\Factories\OAuth\OAuthClientFactory;

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

    public function testGetNotAdmin(): void
    {
        $this->oauthClient = OAuthClientFactory::createOne();

        $forum = ForumFactory::createOne();

        $this->expectException(ClientException::class);
        $this->expectExceptionCode(403);
        $this->get(self::endpoint() . '/' . $forum->getId());
    }

    public function testGetCollectionNotAdmin(): void
    {
        $this->oauthClient = OAuthClientFactory::createOne();

        ForumFactory::createMany(3);

        $response = $this->getCollection(self::endpoint());

        self::assertEmpty($response);
    }
}
