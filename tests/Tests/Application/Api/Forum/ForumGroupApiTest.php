<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Api\Forum;

use Forumify\Testing\Api\ApiTestCase;
use Forumify\Testing\Api\Crud\DeleteTestTrait;
use Forumify\Testing\Api\Crud\GetCollectionTestTrait;
use Forumify\Testing\Api\Crud\GetTestTrait;
use Forumify\Testing\Api\Crud\PatchTestTrait;
use Forumify\Testing\Api\Crud\PostTestTrait;
use Forumify\Testing\Factories\Forum\ForumGroupFactory;

class ForumGroupApiTest extends ApiTestCase
{
    use GetTestTrait;
    use GetCollectionTestTrait;
    use PostTestTrait;
    use PatchTestTrait;
    use DeleteTestTrait;

    protected static function factory(): string
    {
        return ForumGroupFactory::class;
    }

    protected static function endpoint(): string
    {
        return '/api/forums/forum-groups';
    }

    protected function getPostBody(): array
    {
        return ['title' => 'hello group'];
    }

    protected function getPatchBody(): array
    {
        return ['title' => 'updated group'];
    }
}
