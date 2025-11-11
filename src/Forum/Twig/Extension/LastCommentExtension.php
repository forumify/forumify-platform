<?php

declare(strict_types=1);

namespace Forumify\Forum\Twig\Extension;

use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Entity\Forum;
use Forumify\Forum\Entity\Topic;
use Forumify\Forum\Service\LastCommentService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class LastCommentExtension extends AbstractExtension
{
    public function __construct(private readonly LastCommentService $lastCommentService)
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('last_comment', $this->getLastComment(...)),
        ];
    }

    public function getLastComment(Forum|Topic $subject): ?Comment
    {
        if (($subject instanceof Forum) && !$subject->getDisplaySettings()->isShowLastCommentBy()) {
            return null;
        }

        if (($subject instanceof Topic) && !$subject->getForum()->getDisplaySettings()->isShowTopicLastCommentBy()) {
            return null;
        }

        return $this->lastCommentService->getLastComment($subject);
    }
}
