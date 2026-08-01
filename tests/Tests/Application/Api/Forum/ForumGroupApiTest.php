<?php

declare(strict_types=1);

namespace Tests\Tests\Application\Api\Forum;

use Tests\Tests\Application\Api\ApiTestCase;
use Tests\Tests\Application\Api\Crud\DeleteTestTrait;
use Tests\Tests\Application\Api\Crud\GetCollectionTestTrait;
use Tests\Tests\Application\Api\Crud\GetTestTrait;
use Tests\Tests\Application\Api\Crud\PatchTestTrait;
use Tests\Tests\Application\Api\Crud\PostTestTrait;
use Tests\Tests\Factories\Forum\ForumGroupFactory;

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
