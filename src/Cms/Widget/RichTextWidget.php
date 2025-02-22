<?php

declare(strict_types=1);

namespace Forumify\Cms\Widget;

class RichTextWidget implements WidgetInterface
{
    public function getName(): string
    {
        return 'content.rich_text';
    }

    public function getCategory(): string
    {
        return 'content';
    }

    public function getPreview(): string
    {
        return '<article class="text-small">
            Rich Text
        </article>';
    }

    public function getTemplate(): string
    {
        return '';
    }
}
