<?php

declare(strict_types=1);

namespace Forumify\Core\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class RichTextExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('rich', $this->richText(...), ['is_safe' => ['html']]),
        ];
    }

    private function richText(string $content): string
    {
        return "<div class='rich-text'>$content</div>";
    }
}
