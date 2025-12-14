<?php

declare(strict_types=1);

namespace Forumify\Core\Service;

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
        'u',
        'em',
        'blockquote',
        'code',
        'pre',
        'ul',
        'ol',
        'li',
        'table',
        'thead',
        'tbody',
        'tr',
        'th',
        'td',
    ];

    public function sanitize(string $html): string
    {
        return strip_tags($html, self::TAG_WHITELIST);
    }
}
