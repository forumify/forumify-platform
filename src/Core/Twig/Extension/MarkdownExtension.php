<?php

declare(strict_types=1);

namespace Forumify\Core\Twig\Extension;

use Forumify\Core\Service\MarkdownParser;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MarkdownExtension extends AbstractExtension
{
    public function __construct(private readonly MarkdownParser $parser)
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('markdown', $this->parser->parse(...), [
                'is_safe' => ['html'],
            ]),
        ];
    }
}
