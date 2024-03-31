<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

// TODO: sanitize html haha
class HTMLSanitizer
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

    public function sanitize(string $html): string
    {
        return $html;
    }
}
