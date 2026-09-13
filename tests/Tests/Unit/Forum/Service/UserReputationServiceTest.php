<?php

declare(strict_types=1);

namespace Tests\Tests\Unit\Forum\Service;

use Forumify\Forum\Service\UserReputationService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Forumify\Testing\Factories\Core\UserFactory;
use Forumify\Testing\Factories\Forum\CommentFactory;
use Forumify\Testing\Factories\Forum\CommentReactionFactory;
use Forumify\Testing\Factories\Forum\ReactionFactory;
use Zenstruck\Foundry\Test\Factories;

class UserReputationServiceTest extends KernelTestCase
{
    use Factories;

    public function testGetReputation(): void
    {
        $authorUser = UserFactory::createOne();
        $otherAuthorUser = UserFactory::createOne();
        $reactorUser1 = UserFactory::createOne();
        $reactorUser2 = UserFactory::createOne();

        // Author's comment with reactions from others
        $authorComment = CommentFactory::createOne(['createdBy' => $authorUser]);
        $reaction1 = ReactionFactory::createOne(['reputation' => 10]);
        $reaction2 = ReactionFactory::createOne(['reputation' => 20]);

        CommentReactionFactory::createOne([
            'comment' => $authorComment,
            'user' => $reactorUser1,
            'reaction' => $reaction1,
        ]);

        CommentReactionFactory::createOne([
            'comment' => $authorComment,
            'user' => $reactorUser2,
            'reaction' => $reaction2,
        ]);

        // Reaction from author themselves should not count
        $authorReactionToOwnComment = ReactionFactory::createOne(['reputation' => 100]);
        CommentReactionFactory::createOne([
            'comment' => $authorComment,
            'user' => $authorUser,
            'reaction' => $authorReactionToOwnComment,
        ]);

        // Comment from another user should not count
        $otherComment = CommentFactory::createOne(['createdBy' => $otherAuthorUser]);
        $otherReaction = ReactionFactory::createOne(['reputation' => 50]);
        CommentReactionFactory::createOne([
            'comment' => $otherComment,
            'user' => $reactorUser1,
            'reaction' => $otherReaction,
        ]);

        $service = self::getContainer()->get(UserReputationService::class);
        $reputation = $service->getReputation($authorUser);

        self::assertEquals(30, $reputation);
    }
}
