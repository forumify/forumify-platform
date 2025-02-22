<?php

declare(strict_types=1);

namespace Forumify\Cms\Widget;

class OneColumnLayout implements WidgetInterface
{
    public function getName(): string
    {
        return 'layout.one_column';
    }

    public function getCategory(): string
    {
        return 'layout';
    }

    public function getPreview(): string
    {
        return '<div class="grid-1">
            <div class="col-1 widget-slot"></div>
        </div>';
    }

    public function getTemplate(): string
    {
        return '';
    }
}
