<?php

declare(strict_types=1);

namespace Forumify\Forum\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ForumExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('last_comment', [ForumExtensionRuntime::class, 'getLastComment']),
            new TwigFilter('can_mark_as_answer', [ForumExtensionRuntime::class, 'canMarkAsAnswer']),
            new TwigFilter('user_reputation', [ForumExtensionRuntime::class, 'getUserReputation']),
        ];
    }
}
