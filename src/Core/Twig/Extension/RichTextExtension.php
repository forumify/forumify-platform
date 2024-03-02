<?php

declare(strict_types=1);

namespace Forumify\Core\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class RichTextExtension extends AbstractExtension
{
    private const TAG_WHITELIST = [
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'div',
        'p',
        'span',
        'br',
        'a',
        'img',
        'strong',
        'em',
        'blockquote',
        'code',
        'pre',
        'ul',
        'ol',
        'li',
    ];

    private const ATTR_WHITELIST = [
        'class',
        'style',
        'href',
        'src',
        'alt',
    ];

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
