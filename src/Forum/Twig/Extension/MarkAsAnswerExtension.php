<?php

declare(strict_types=1);

namespace Forumify\Forum\Twig\Extension;

use Forumify\Core\Security\VoterAttribute;
use Forumify\Forum\Entity\Comment;
use Forumify\Forum\Entity\Forum;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MarkAsAnswerExtension extends AbstractExtension
{
    public function __construct(private readonly Security $security)
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('can_mark_as_answer', $this->canMarkAsAnswer(...)),
        ];
    }

    public function canMarkAsAnswer(Comment $comment): bool
    {
        $topic = $comment->getTopic();
        if ($topic->getForum()->getType() !== Forum::TYPE_SUPPORT) {
            // only available in support forums
            return false;
        }

        if ($comment->getId() === $topic->getFirstComment()?->getId()) {
            // The first comment should always be the question
            return false;
        }

        if ($comment->getId() === $topic->getAnswer()?->getId()) {
            // already marked as the answer
            return false;
        }

        return $this->security->isGranted(VoterAttribute::CommentMarkAsAnswer->value, $comment);
    }
}
