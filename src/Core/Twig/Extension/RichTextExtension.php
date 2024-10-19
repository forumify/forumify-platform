<?php

declare(strict_types=1);

namespace Forumify\Core\Twig\Extension;

use Forumify\Core\Service\HTMLSanitizer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class RichTextExtension extends AbstractExtension
{
    public function __construct(private readonly HTMLSanitizer $sanitizer)
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('rich', $this->richText(...), ['is_safe' => ['html']]),
        ];
    }

    private function richText(string $content): string
    {
        $sanitized = $this->sanitizer->sanitize($content);
        return "<div class='rich-text' data-controller='forumify--forumify-platform--rich-text'>$sanitized</div>";
    }
}
